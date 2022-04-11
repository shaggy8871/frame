<?php

/*
 * This is where most of the magic happens
 */

namespace Frame\Core;

use Frame\Core\Exception\ConfigException;
use Frame\Core\Exception\RouteNotFoundException;
use Frame\Core\Exception\UnknownAliasException;
use Frame\Core\Exception\ClassNotFoundException;

class Router
{

    const ROUTE_RESOLVER = 'routeResolver';
    const ROUTE_NOTFOUND = 'routeNotFound';
    const ROUTE_DEFAULT  = 'routeDefault';

    private $project;
    private $url;
    private $caller;

    public function __construct(Project $project, Url $url = null)
    {

        $this->project = $project;
        $this->url = $url;

    }

    /*
     * Determine the target controller based on the url path
     * @todo Add debugging information to see how route is determined
     */
    public function parseUrl(Url $url = null)
    {

        if ($url) {
            $this->url = $url; // override existing
        }

        if (!($this->url instanceof Url)) {
            throw new ConfigException('No Url instance supplied for parser.');
        }

        $pathComponents = $this->url->pathComponents;

        // Iterate through each and convert to class or method name
        foreach($pathComponents as &$pathComponent) {
            $pathComponent = str_replace('-', '_', ucfirst($pathComponent));
        }

        $projectControllers = $this->project->ns . '\\Controllers\\';

        // Attempt 1: Look for Routes class in project and call routeResolver method
        $method = self::ROUTE_RESOLVER;
        $controller = $this->project->ns . '\\Routes';
        if (method_exists($controller, $method)) {

            // Call the project routeResolver method
            $route = call_user_func(array(new $controller($this->project), $method), $this->url);

            // If we get a class name back, look for another routeResolver method within
            if ((is_string($route)) && (strpos($route, '::') === false) && (class_exists($projectControllers . $route)) && (method_exists($projectControllers . $route, $method))) {
                $savedRoute = $route;
                $routeController = $projectControllers . $route;
                $controllerClass = new $routeController($this->project);
                // Call routeResolver in the controller class
                $route = call_user_func(array($controllerClass, $method), $this->url);
                // If I get a partial string result, assume it's a method response, otherwise prepare for fallback
                if ((is_string($route)) && (strpos($route, '::') === false) && (is_callable(array($controllerClass, $route)))) {
                    return $this->invokeClassMethod($controllerClass, $route);
                } else {
                    $pathComponents[0] = $savedRoute;
                }
            }

            // If we get a string back in format $controller::$method, look for the method
            // If the return class method starts with "\" char, look outside the project controller tree
            if ((is_string($route)) && (strpos($route, '::') !== false)) {
                list($controller, $method) = explode('::', ($route[0] != '\\' ? $projectControllers : '') . $route);
                if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                    return $this->invokeClassMethod(new $controller($this->project), $method);
                }
            }

            // Otherwise, if we get a closure back, call it
            if (is_callable($route)) {
                if ((is_array($route)) && (count($route) == 2)) {
                    return $this->invokeClassMethod($route[0], $route[1]);
                } else {
                    $reflection = new \ReflectionFunction($route);
                    if ($reflection->isClosure()) {
                        return $this->invokeFunction($route);
                    }
                }
            }

        }

        // Attempt 2: pointing to a controller with a routeResolver method
        $path = $pathComponents;
        $method = self::ROUTE_RESOLVER;
        $controller = $projectControllers . (empty($path) ? 'Index' : $path[0]);
        $controllerClass = (class_exists($controller) ? new $controller($this->project) : null);
        if (($controllerClass) && (method_exists($controllerClass, $method))) {

            // Call routeResolver in the controller class
            $route = call_user_func(array($controllerClass, $method), $this->url);

            // If we get a string back in format $controller::$method, look for the method
            // If the return class method starts with "\" char, look outside the project controller tree
            if ((is_string($route)) && (strpos($route, '::') !== false)) {
                list($controller, $method) = explode('::', ($route[0] != '\\' ? $projectControllers : '') . $route);
                if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                    return $this->invokeClassMethod(new $controller($this->project), $method);
                }
            }

            // If we get a partial string result, assume it's a method response
            if ((is_string($route)) && (strpos($route, '::') === false) && (is_callable(array($controllerClass, $route)))) {
                return $this->invokeClassMethod($controllerClass, $route);
            }

            // Otherwise, if we get a closure back, call it
            if (is_callable($route)) {
                if ((is_array($route)) && (count($route) == 2)) {
                    return $this->invokeClassMethod($route[0], $route[1]);
                } else {
                    $reflection = new \ReflectionFunction($route);
                    if ($reflection->isClosure()) {
                        return $this->invokeFunction($route);
                    }
                }
            }

        }

        // Attempt 3: pointing to a specific route* method within the controller
        if (count($pathComponents) > 1) {
            $path = $pathComponents;
            $controllerClass = array_shift($path);
            $methodName = array_shift($path);
            $method = ($methodName != null ? 'route' . $methodName : self::ROUTE_DEFAULT);
            $controller = $this->findController($controllerClass);
            if ($controller) {
                $methodFound = $this->findMethod($controller, $method);
                if ($methodFound) {
                    return $methodFound;
                }
            }
        }

        // Attempt 4: check for a controller with routeDefault method
        if (count($pathComponents) == 1) {
            $path = $pathComponents;
            $lookupName = array_shift($path);
            $method = self::ROUTE_DEFAULT;
            $controller = $this->findController($lookupName);
            if ($controller) {
                $methodFound = $this->findMethod($controller, $method);
                if ($methodFound) {
                    return $methodFound;
                }
            }
        }

        // Attempt 5: look for a method in the Index controller
        $path = $pathComponents;
        $lookupName = array_shift($path);
        $method = ($lookupName ? 'route' . $lookupName : self::ROUTE_DEFAULT);
        $controller = $this->findController('Index');
        if ($controller) {
            $methodFound = $this->findMethod($controller, $method);
            if ($methodFound) {
                return $methodFound;
            }
        }

        // Can't determine route, so start fallback steps
        return $this->routeNotFound();

    }

    /*
     * When a route cannot be determined, fall back in a controlled sequence
     */
    private function routeNotFound()
    {

        // @todo: Remove duplicate code above/below
        $pathComponents = $this->url->pathComponents;

        // Iterate through each and convert to class or method name
        foreach($pathComponents as &$pathComponent) {
            $pathComponent = str_replace('-', '_', ucfirst($pathComponent));
        }

        // Attempt 1: if we have a controller class, look for a routeNotFound method
        $path = $pathComponents;
        $method = self::ROUTE_NOTFOUND;
        $controller = $this->findController((empty($path) ? 'Index' : $path[0]));
        if ((class_exists($controller)) && (is_callable($controller . '::' . $method))) {
            return (new $controller($this->project))->$method($this->url, $this->project);
        }

        // Finally, fail with an exception that can be trapped and handled
        throw new RouteNotFoundException($this->url, $this->project);

    }

    /*
     * Calls the specified class method and injects parameters
     * @param $class controller class object
     * @param $method string method name
     * @todo Instantiate parameters only once per global session
     */
    private function invokeClassMethod($class, $method)
    {

        if (!is_callable(array($class, $method))) {
            return $this->routeNotFound();
        }

        $reflection = new \ReflectionMethod($class, $method);

        // Look up docblock annotations if available
        if ($reflection->getDocComment()) {
            $annotations = Utils\Annotations::parseDocBlock($reflection->getDocComment());
        } else {
            $annotations = null;
        }

        // Save caller information
        $this->caller = new Caller($class, $method, $annotations);

        // Call the method
        $this->invokeCallable($reflection, $class);

    }

    /*
     * Calls the specified function or closure and injects parameters
     * @param $function the closure
     */
    private function invokeFunction($function)
    {

        if (!is_callable($function)) {
            return $this->routeNotFound();
        }

        $reflection = new \ReflectionFunction($function);

        // Look up docblock annotations if available
        if ($reflection->getDocComment()) {
            $annotations = Utils\Annotations::parseDocBlock($reflection->getDocComment());
        } else {
            $annotations = null;
        }

        // Save caller information
        $this->caller = new Caller(null, $function, $annotations);

        // Call the function
        $this->invokeCallable($reflection);

    }

    /*
     * Calls the method or closure and injects parameters dynamically
     */
    private function invokeCallable($reflection, $class = null)
    {

        $this->beforeCall($reflection, $class);

        // Get an array of ReflectionParameter objects
        $params = $reflection->getParameters();
        // Injection array
        $inject = [];
        // Find first response class to set it as default
        $defaultResponseClass = null;
        // Loop through parameters to determine their class types
        foreach($params as $param) {
            try {
                // Use of getClass() is deprecated in PHP 8
                // So we would have to use getType()->getName()
                // and instantiate the reflection class
                if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
                    $paramClass = new \ReflectionClass($param->getType()->getName());
                } else {
                    $paramClass = $param->getClass();
                }
            } catch (\Exception $e) {
                // Rethrow the error with further information
                throw new ClassNotFoundException($param->getName(), ($this->caller->controller ? get_class($this->caller->controller) : null), $this->caller->method);
            }
            // If it's not a class, inject a null value
            if (!($paramClass instanceof \ReflectionClass)) {
                $inject[] = null;
                continue;
            }
            // Special case for a Url and Project type hints, send in the one we already have
            if ($paramClass->name == 'Frame\\Core\\Url') {
                $inject[] = $this->url;
            } else
            if ($paramClass->name == 'Frame\\Core\\Project') {
                $inject[] = $this->project;
            } else
            if ($paramClass->name == 'Frame\\Core\\Context') {
                $inject[] = new Context($this->project, $this->url, $this->caller);
            } else {
                if ($this->isRequestClass($paramClass->name, false)) {
                    $paramInstance = $this->instantiateRequestClass($param, $paramClass);
                } else {
                    $paramInstance = new $paramClass->name(
                        new Context($this->project, $this->url, $this->caller)
                    );
                }
                // If this is a response class, set the default view filename
                if ($this->isResponseClass($paramInstance)) {
                    if (($class) && (is_callable(array($paramInstance, 'setDefaults')))) {
                        $this->setResponseDefaults($paramInstance, $reflection, $class);
                    }
                    // Set the default response class if one isn't already set
                    if (!$defaultResponseClass) {
                        $defaultResponseClass = $paramInstance;
                    }
                }
                $inject[] = $paramInstance;
            }
        }

        // Send the injected parameters into the identified method
        if ($reflection instanceof \ReflectionMethod) {
            $response = $reflection->invokeArgs($class, $inject);
        } else {
            $response = $reflection->invokeArgs($inject);
        }

        $this->afterCall($response);

        if (($response !== false) && ($response !== null)) {
            // If object is a Response class, simply call the render method (assume it knows what to do)
            // Otherwise call the render method on the defined/default response class
            if ((is_object($response)) && ($this->isResponseClass($response))) {
                $response->render();
            } else {
                // If we have a default response class set, use it
                if ($defaultResponseClass) {
                    $responseClass = $defaultResponseClass;
                } else {
                    $responseClass = new \Frame\Response\Html(
                        new Context($this->project, $this->url, $this->caller)
                    );
                    if (is_callable(array($responseClass, 'setDefaults'))) {
                        $this->setResponseDefaults($responseClass, $reflection, $class);
                    }
                }
                if (is_callable(array($responseClass, 'render'))) {
                    $responseClass->render($response);
                }
            }
        }

    }

    /**
     * Allows a @before annotation to determine a different route
     */
    private function beforeCall(&$reflection, &$class)
    {

        if (!isset($this->caller->annotations['before'])) {
            return; // proceed
        }

        $route = $this->caller->annotations['before'];
        $projectControllers = $this->project->ns . '\\Controllers\\';
        $beforeClass = null;
        $beforeReflection = null;

        // If we get a string back in format $controller::$method, look for the method
        // If the return class method starts with "\" char, look outside the project controller tree
        if ((is_string($route)) && (strpos($route, '::') !== false)) {
            list($controller, $method) = explode('::', ($route[0] != '\\' ? $projectControllers : '') . $route);
            if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                $beforeClass = new $controller($this->project);
                $beforeReflection = new \ReflectionMethod($beforeClass, $method);
            }
        } else
        // If we get a method name back, look in the same class
        if ((is_string($route)) && (method_exists($class, $route))) {
            $beforeClass = $class;
            $beforeReflection = new \ReflectionMethod($class, $route);
        } else
        // Otherwise if it's callable, it must be a function
        if (is_callable($route)) {
            $beforeClass = $class;
            $beforeReflection = new \ReflectionFunction($route);
        }

        if (!($beforeReflection instanceof \ReflectionFunctionAbstract)) {
            return; // ignore; @todo: log reason
        }

        // Get an array of ReflectionParameter objects
        $params = $beforeReflection->getParameters();
        // Injection array
        $inject = [];
        // Loop through parameters to determine their class types
        foreach($params as $param) {
            try {
                // Use of getClass() is deprecated in PHP 8
                // So we would have to use getType()->getName()
                // and instantiate the reflection class
                if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
                    $paramClass = new \ReflectionClass($param->getType()->getName());
                } else {
                    $paramClass = $param->getClass();
                }
            } catch (\Exception $e) {
                // Rethrow the error with further information
                throw new ClassNotFoundException($param->getName(), ($this->caller->controller ? get_class($this->caller->controller) : null), $this->caller->method);
            }
            // If it's not a class, inject a null value
            if (!($paramClass instanceof \ReflectionClass)) {
                $inject[] = null;
                continue;
            }
            // Special case for a Url and Project type hints, send in the one we already have
            if ($paramClass->name == 'Frame\\Core\\Url') {
                $inject[] = $this->url;
            } else
            if ($paramClass->name == 'Frame\\Core\\Project') {
                $inject[] = $this->project;
            } else
            if ($paramClass->name == 'Frame\\Core\\Context') {
                $inject[] = new Context($this->project, $this->url, $this->caller);
            } else {
                $inject[] = null;
            }
        }

        // Send the injected parameters into the identified method
        if ($beforeReflection instanceof \ReflectionMethod) {
            $response = $beforeReflection->invokeArgs($beforeClass, $inject);
        } else {
            $response = $beforeReflection->invokeArgs($inject);
        }

        // If we get a string back in format $controller::$method, look for the method
        // If the return class method starts with "\" char, look outside the project controller tree
        if ((is_string($response)) && (strpos($response, '::') !== false)) {
            list($controller, $method) = explode('::', ($response[0] != '\\' ? $projectControllers : '') . $response);
            if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                // Override parameters:
                $class = new $controller($this->project);
                $reflection = new \ReflectionMethod($class, $method);
            }
        } else
        // If we get a method name back, look in the same class
        if ((is_string($response)) && (method_exists($class, $response))) {
            $reflection = new \ReflectionMethod($class, $response);
        } else
        // Otherwise, if we get a closure back, call it
        if (is_callable($response)) {
            if ((is_array($response)) && (count($response) == 2)) {
                // Override parameters:
                $class = new $response[0];
                $reflection = new \ReflectionMethod($response[0], $response[1]);
            } else {
                $reflection = new \ReflectionFunction($response);
            }
        }

    }

    /*
     * @todo
     */
    private function afterCall(&$response)
    {
    }

    /*
     * Returns true if it's a Request class
     */
    private function isRequestClass($class, $autoload = true)
    {

        return in_array('Frame\\Request\\RequestInterface', (array) class_implements($class, $autoload));

    }

    /*
     * Returns true if it's a Response class
     */
    private function isResponseClass($class, $autoload = true)
    {

        return in_array('Frame\\Response\\ResponseInterface', class_implements($class, $autoload));

    }

    /*
     * Special case for request parameters
     * If the parameter class contains a static createFromRequest method,
     * ask it to instantiate the class for us using the request data supplied.
     */
    private function instantiateRequestClass($param, $paramClass)
    {

        try {

            $paramFactory = $paramClass->getMethod('createFromRequest');
            // Method exists, but is it static?
            if (!$paramFactory->isStatic()) {
                // Fall back
                return new $paramClass->name(
                    new Context($this->project, $this->url, $this->caller)
                );
            }

            $paramInstance = $paramFactory->invoke(null, new \Frame\Request\Request(
                new Context($this->project, $this->url, $this->caller)
            ));

            // If we don't get an object back, set it to null for safety
            if (!is_object($paramInstance)) {
                $paramInstance = null;
            }

            // If the parameter doesn't allow null values, throw an error to prevent
            // the compiler from doing so
            if (($paramInstance == null) && (!$param->allowsNull())) {
                throw new ConfigException("Method " . $paramClass->name . "::createFromRequest returned null or a non-object, and Request parameter does not accept nulls.");
            }

            return $paramInstance;

        } catch (\ReflectionException $e) {
            // Didn't work so continue as normal
            return new $paramClass->name(
                new Context($this->project, $this->url, $this->caller)
            );
        }

    }

    /*
     * Copied and modified from http://php.net/manual/en/reflectionparameter.getclass.php#108620
     */
    private function getParamClassName(\ReflectionParameter $param)
    {

        preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', $param->__toString(), $matches);
        return isset($matches[1]) ? $matches[1] : null;

    }

    /*
     * Inject details into the response class. Not available for closures.
     */
    private function setResponseDefaults($responseClass, $reflection, $controllerClass = null)
    {

        if (!is_callable(array($responseClass, 'setDefaults'))) {
            return false;
        }
        // Not available if it's a closure since we have no context
        if ($reflection->isClosure()) {
            return false;
        }

        // Try to auto-detect details using controller
        if ($controllerClass) {
            // Reflect on the controllerClass
            $controllerClassReflection = new \ReflectionClass($controllerClass);
            $controllerPath = pathinfo($controllerClassReflection->getFileName());
            // Inject view filename
            $responseClass->setViewFilename($controllerPath['filename'] . '/' . strtolower(str_replace('route', '', $reflection->getName())));
        }

    }

    /*
     * Find a controller that matches the name specified
     */
    private function findController($controller)
    {

        if (!$controller) {
            return false;
        }

        $projectControllers = $this->project->ns . '\\Controllers\\';

        if (class_exists($projectControllers . $controller)) {
            return $projectControllers . $controller;
        }

        // Fallback
        $glob = '';
        $controllerLength = strlen($controller);
        for($i = 0; $i < $controllerLength; $i++) {
            $glob .= '[' . strtolower($controller[$i]) . strtoupper($controller[$i]) . ']';
        }
        $glob = $this->project->path . '/Controllers/' . $glob . '.php';

        // Use glob range search to find a case insensitive match
        $match = glob($glob, GLOB_NOSORT);
        if ($match) {
            return $projectControllers . basename(array_shift($match), '.php');
        }

    }

    /*
     * Attempt to find the appropriate method to call
     */
    private function findMethod($controller, $method)
    {

        if (method_exists($controller, $method)) {
            $this->invokeClassMethod(new $controller($this->project), $method);
            // Return true to indicate that a method is found
            return true;
        }

        $methodMatch = $this->scanForMethodMatches($controller);
        if ($methodMatch) {
            $this->invokeClassMethod(new $controller($this->project), $methodMatch);
            // Return true to indicate that a method is found
            return true;
        }

        return false;

    }

    /*
     * Find a matching method using annotation matching
     */
    private function scanForMethodMatches($controller)
    {

        $controllerClassReflection = new \ReflectionClass($controller);
        $methods = $controllerClassReflection->getMethods();
        foreach($methods as $method) {
            if ($method->getDocComment()) {
                $annotation = Utils\Annotations::parseDocBlock($method->getDocComment());
                if (isset($annotation['canonical'])) {
                    $canonical = Utils\Url::templateToRegex($annotation['canonical'], $keys);
                    // Return as soon as a match is found
                    if (preg_match($canonical, $this->url->getRequestUri())) {
                        return $method->getName();
                    }
                }
            }
        }

    }

    /*
     * Creates a namespace alias only if it doesn't already exist
     */
    private function createAlias($from, $to)
    {

        if ((class_exists($from)) && (!class_exists($to))) {
            class_alias($from, $to);
        }

    }

    /*
     * Return the project
     */
    public function getProject()
    {

        return $this->project;

    }

    /*
     * Return the parsed url class that we're using
     */
    public function getUrl()
    {

        return $this->url;

    }

    /*
     * Return the caller information
     */
    public function getCaller()
    {

        return $this->caller;

    }

}
