<?php

/*
 * This is where most of the magic happens
 */

namespace Frame\Core;

class Router
{

    const REQUEST = 0;
    const RESPONSE = 1;

    private $project;
    private $url;
    private $debug;
    // Order of namespace aliases for request/response parameters
    private $paramAliases = [
        'Frame\\Request',
        'Frame\\Response'
    ];

    public function __construct(Init $init = null)
    {

        if (php_sapi_name() == 'cli') {
            // @todo Handle console apps
            $this->project = $this->getProjectFromArgs();
        } else {
            // Handle web apps
            $this->url = new Url();
            $this->project = $this->getProjectFromHost($init->projects);
            $this->parseUrlPathComponents();
        }

    }

    /*
     * Determine the project folder from the hostname
     */
    private function getProjectFromHost($projects)
    {

        if (array_key_exists($this->url->host, $projects)) {
            return $this->getProject($projects[$this->url->host]);
        } else {
            throw new \Exception('Cannot determine project path from host ' . $this->url->host);
        }

    }

    /*
     * Determine the project folder from the first CLI argument
     */
    private function getProjectFromArgs()
    {

        if ((isset($GLOBALS['argv'])) && (count($GLOBALS['argv']) > 1) && (file_exists($GLOBALS['argv'][1]))) {
            return $this->getProject($GLOBALS['argv'][1]);
        } else {
            throw new \Exception('Cannot determine project path from argument ' . $GLOBALS['argv'][1]);
        }

    }

    /*
     * Creates a project object
     */
    private function getProject($ns)
    {

        // Do we have a configuration class?
        $configClass = $ns . '\\Config';
        $config = (class_exists($configClass) ? new $configClass() : null);

        return (object) array(
            'ns' => $ns,
            'config' => $config,
        );

    }

    /*
     * Determine the target controller based on the url path
     * @todo Add debugging information to see how route is determined
     */
    private function parseUrlPathComponents()
    {

        $pathComponents = $this->url->pathComponents;

        // Iterate through each and convert to class or method name
        foreach($pathComponents as &$pathComponent) {
            $pathComponent = ucfirst($pathComponent);
        }

        // Alias in the Url class
        $this->createAlias('Frame\\Core\\Url', $this->project->ns . '\\Url');
        $this->createAlias('Frame\\Core\\Url', $this->project->ns . '\\Controllers\\Url');

        $projectControllers = $this->project->ns . '\\Controllers\\';

        // Attempt 1: Look for Routes class in project and call routeResolver method
        $method = 'routeResolver';
        $controller = $this->project->ns . '\\Routes';
        if (method_exists($controller, $method)) {

            // Call the project routeResolver method
            $route = call_user_func(array(new $controller, $method), $this->url);

            // If we get a class name back, look for another routeResolver method within
            if ((is_string($route)) && (strpos($route, '::') === false) && (class_exists($projectControllers . $route)) && (method_exists($projectControllers . $route, $method))) {
                $savedRoute = $route;
                $routeController = $projectControllers . $route;
                $controllerClass = new $routeController;
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
                    return $this->invokeClassMethod(new $controller, $method);
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
        $method = 'routeResolver';
        $controller = $projectControllers . (empty($path) ? 'Index' : $path[0]);
        if (method_exists($controller, $method)) {

            $controllerClass = new $controller;

            // Call routeResolver in the controller class
            $route = call_user_func(array($controllerClass, $method), $this->url);

            // If we get a string back in format $controller::$method, look for the method
            // If the return class method starts with "\" char, look outside the project controller tree
            if ((is_string($route)) && (strpos($route, '::') !== false)) {
                list($controller, $method) = explode('::', ($route[0] != '\\' ? $projectControllers : '') . $route);
                if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                    return $this->invokeClassMethod(new $controller, $method);
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

        // Attempt 3: pointing to a controller with default route
        $path = $pathComponents;
        $method = 'routeDefault';
        $controller = $projectControllers . (empty($path) ? 'Index' : implode('\\', $path));
        if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
            return $this->invokeClassMethod(new $controller, $method);
        }

        // Attempt 4: pointing to a specific route* method within a controller
        $path = $pathComponents;
        $method = 'route' . array_pop($path);
        $controller = $projectControllers . (empty($path) ? 'Index' : implode('\\', $path));
        if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
            return $this->invokeClassMethod(new $controller, $method);
        }

        // Can't parse
        // @todo Handle this better, with a 404 or exception screen
        throw new Exception\RouteNotFoundException($this->url);

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
            throw new Exception\RouteNotFoundException($this->url);
        }

        $this->invokeCallable(new \ReflectionMethod($class, $method), $class);

    }

    /*
     * Calls the specified function or closure and injects parameters
     * @param $class controller class object
     * @param $method string method name
     * @todo Instantiate parameters only once per global session
     */
    private function invokeFunction($function)
    {

        if (!is_callable($function)) {
            throw new Exception\RouteNotFoundException($this->url);
        }

        $this->invokeCallable(new \ReflectionFunction($function));

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
                    throw new Exception\UnknownAliasException($this->getParamClassName($param), ($class ? get_class($class) : ''), $param->getDeclaringFunction()->name);
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
                    throw new Exception\ClassNotFoundException($alias, ($class ? get_class($class) : ''), $param->getDeclaringFunction()->name);
                }
            }
            // If we get this far, we should have the class aliased and auto-loaded
            if ($paramClass instanceof \ReflectionClass) {
                // Instantiate parameter class and save to injection array
                $paramInstance = new $paramClass->name();
                $paramPos = $param->getPosition();
                // If this is a response class (parameter 2), set the default view filename
                if (($class) && ($paramPos == self::RESPONSE) && (is_callable(array($paramInstance, 'setDefaults')))) {
                    $this->setResponseDefaults($paramInstance, $reflection, $class);
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

        if ($response) {
            // If object is a Response class, simply call the render method (assume it knows what to do)
            // Otherwise call the render method on the defined/default response class
            if ((is_object($response)) && (in_array('Frame\\Response\\ResponseInterface', class_implements($response, true)))) {
                $response->render();
            } else {
                if (array_key_exists(self::RESPONSE, $inject)) {
                    $responseClass = $inject[self::RESPONSE];
                } else {
                    $responseClass = new \Frame\Response\Html();
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
        // Not available if it's a closure
        if ($reflection->isClosure()) {
            return false;
        }

        if ($controllerClass) {
            // Reflect on the controllerClass
            $controllerClassReflection = new \ReflectionClass($controllerClass);
            $controllerPath = pathinfo($controllerClassReflection->getFileName());
            $viewBaseDir = str_replace('/Controllers', '/Views', str_replace('\\', '/', $controllerPath['dirname']));
            $viewDir = $viewBaseDir . '/' . $controllerPath['filename'];
        } else {
            $viewBaseDir = null;
            $viewDir = null;
        }

        // Get view filename
        $viewFilename = str_replace($viewBaseDir . '/', '', $viewDir . '/' . strtolower(str_replace('route', '', $reflection->getName())));

        // Inject defaults into the response class
        $responseClass->setDefaults(array(
            'project' => $this->project,
            'viewFilename' => $viewFilename,
            'viewBaseDir' => $viewBaseDir
        ));

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

}
