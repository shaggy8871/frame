<?php

namespace Frame\Core;

class Router
{

    const REQUEST = 0;
    const RESPONSE = 1;

    private $project;
    private $url;
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
            return $projects[$this->url->host];
        } else {
            throw new \Exception('Cannot determine project path from host ' . $this->url->host);
        }

    }

    /*
     * Determine the project folder from the first CLI argument
     */
    private function getProjectFromArgs()
    {

        if ((isset($GLOBALS['argv'])) && (count($GLOBALS['argv']) > 1) && (file_exists('src/' . $GLOBALS['argv'][1]))) {
            return $GLOBALS['argv'][1];
        } else {
            throw new \Exception('Cannot determine project path from argument ' . $GLOBALS['argv'][1]);
        }

    }

    /*
     * Determine the target controller based on the url path
     */
    private function parseUrlPathComponents()
    {

        $pathComponents = $this->url->pathComponents;

        // Iterate through each and convert to class or method name
        foreach($pathComponents as &$pathComponent) {
            $pathComponent = ucfirst($pathComponent);
        }

        // Alias in the Url class
        $this->createAlias('Frame\\Core\\Url', $this->project . '\\Url');
        $this->createAlias('Frame\\Core\\Url', $this->project . '\\Controllers\\Url');

        $projectControllers = $this->project . '\\Controllers\\';

        // Attempt 1: Look for Routes class in project and call routeResponder method
        $method = 'routeResponder';
        $controller = $this->project . '\\Routes';
        if (((class_exists($controller))) && (is_callable($controller . '::' . $method, true))) {

            // Call the project routeResponder method
            $route = call_user_func(array(new $controller, $method), $this->url);

            // If we get a class name back, look for another routeResponder method within
            if ((is_string($route)) && (strpos($route, '::') === false) && (class_exists($projectControllers . $route))) {
                $routeController = $projectControllers . $route;
                $controllerClass = new $routeController;
                // Call routeResponder in the controller class
                $route = call_user_func(array($controllerClass, $method), $this->url);
                // Otherwise, if I get a partial string result, assume it's a method response
                if ((is_string($route)) && (strpos($route, '::') === false) && (is_callable(array($controllerClass, $route)))) {
                    return $this->invokeClassMethod($controllerClass, $route);
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

        // Attempt 2: pointing to a controller with default route
        $path = $pathComponents;
        $method = 'routeDefault';
        $controller = $projectControllers . (empty($path) ? 'Index' : implode('\\', $path));
        if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
            return $this->invokeClassMethod(new $controller, $method);
        }

        // Attempt 3: pointing to a specific route* method within a controller
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
                $inject[] = new $paramClass->name();
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
            if ((is_object($response)) && (in_array('Frame\Response\ResponseInterface', class_implements($response, true)))) {
                $response->render();
            } else {
                $responseClass = (array_key_exists(self::RESPONSE, $inject) ? $inject[self::RESPONSE] : new \Frame\Response\Html());
                $responseClass->render($response);
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
     * Creates a namespace alias only if it doesn't already exist
     */
    private function createAlias($from, $to)
    {

        if ((class_exists($from)) && (!class_exists($to))) {
            class_alias($from, $to);
        }

    }

}
