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
    const PARAM_REQUEST  = 0;
    const PARAM_RESPONSE = 1;

    private $project;
    private $url;
    private $caller;
    // Order of namespace aliases for request/response parameters
    private $paramAliases = [
        'Frame\\Request',
        'Frame\\Response'
    ];

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
        if (method_exists($controller, $method)) {

            $controllerClass = new $controller($this->project);

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
            $controller = $projectControllers . $controllerClass;
            if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                return $this->invokeClassMethod(new $controller($this->project), $method);
            }
        } else {
            $path = $pathComponents;
            $lookupName = array_shift($path);
            // Attempt 3.1: check for a controller with routeDefault method
            $method = self::ROUTE_DEFAULT;
            $controller = $projectControllers . $lookupName;
            if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                return $this->invokeClassMethod(new $controller($this->project), $method);
            }
            // Attempt 3.2: look for a method in the Index controller
            $method = ($lookupName ? 'route' . $lookupName : self::ROUTE_DEFAULT);
            $controller = $projectControllers . 'Index';
            if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                return $this->invokeClassMethod(new $controller($this->project), $method);
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

        $projectControllers = $this->project->ns . '\\Controllers\\';

        // Attempt 1: if we have a controller class, look for a routeNotFound method
        $path = $pathComponents;
        $method = self::ROUTE_NOTFOUND;
        $controller = $projectControllers . (empty($path) ? 'Index' : $path[0]);
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

        // Get an array of ReflectionParameter objects
        $params = $reflection->getParameters();
        // Injection array
        $inject = [];
        // Loop through parameters to determine their class types
        foreach($params as $param) {
            try {
                $paramClass = $param->getClass();
            } catch (\ReflectionException $e) {
                // Try to alias it in
                $matched = preg_match('/Class ([A-Za-z0-9_\\\-]+) does not exist/', $e->getMessage(), $matches);
                if (!$matched) {
                    // Re-throw error
                    throw new \Exception($e->getMessage);
                }
                // What number parameter is this?
                $paramPos = $param->getPosition();
                // Do I have an alias for this parameter?
                if (!isset($this->paramAliases[$paramPos])) {
                    throw new UnknownAliasException($this->getParamClassName($param), ($class ? get_class($class) : ''), $param->getDeclaringFunction()->name);
                }
                // Determine the alias
                $aliasNamespace = explode('\\', $matches[1]);
                $aliasClass = array_pop($aliasNamespace);
                $alias = $this->paramAliases[$paramPos] . '\\' . $aliasClass;
                // Create alias
                $this->createAlias($alias, $matches[1]);
                // Try to get the class again
                try {
                    $paramClass = $param->getClass();
                } catch (\ReflectionException $e) {
                    throw new ClassNotFoundException($alias, ($class ? get_class($class) : ''), $param->getDeclaringFunction()->name);
                }
            }
            // If we get this far, we should have the class aliased and auto-loaded
            if ($paramClass instanceof \ReflectionClass) {
                // Special case for a Url and Project type hints, send in the one we already have
                if ($paramClass->name == 'Frame\\Core\\Url') {
                    $inject[] = $this->url;
                } else
                if ($paramClass->name == 'Frame\\Core\\Project') {
                    $inject[] = $this->url;
                } else
                if ($paramClass->name == 'Frame\\Core\\Context') {
                    $inject[] = new Context($this->project, $this->url, $this->caller);
                } else {
                    $paramPos = $param->getPosition();
                    if ($paramPos == self::PARAM_REQUEST) {
                        $paramInstance = $this->instantiateRequestClass($param, $paramClass);
                    } else {
                        $paramInstance = new $paramClass->name(
                            new Context($this->project, $this->url, $this->caller)
                        );
                    }
                    // If this is a response class (parameter 2), set the default view filename
                    if (($class) && ($paramPos == self::PARAM_RESPONSE) && (is_callable(array($paramInstance, 'setDefaults')))) {
                        $this->setResponseDefaults($paramInstance, $reflection, $class);
                    }
                    $inject[] = $paramInstance;
                }
            }
        }

        // Send the injected parameters into the identified method
        if ($reflection instanceof \ReflectionMethod) {
            $response = $reflection->invokeArgs($class, $inject);
        } else {
            $response = $reflection->invokeArgs($inject);
        }

        if (($response !== false) && ($response !== null)) {
            // If object is a Response class, simply call the render method (assume it knows what to do)
            // Otherwise call the render method on the defined/default response class
            if ((is_object($response)) && (in_array('Frame\\Response\\ResponseInterface', class_implements($response, true)))) {
                $response->render();
            } else {
                if (array_key_exists(self::PARAM_RESPONSE, $inject)) {
                    $responseClass = $inject[self::PARAM_RESPONSE];
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

            // Create a local alias for the Request\Request class
            // @deprecated
            $this->createAlias('Frame\\Request\\Request', $paramClass->getNamespaceName() . '\\Request');

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
