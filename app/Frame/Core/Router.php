<?php

namespace Frame\Core;

class Router
{

    private $project;
    private $url;
    // Order of namespace aliases for parameters
    private $paramAliases = [
        'Frame\\Request',
        'Frame\\Response'
    ];

    public function __construct()
    {

        if (php_sapi_name() == 'cli') {
            // @todo Handle console apps
        } else {
            // Handle web apps
            // @todo determine project dynamically (or via config)
            $this->project = 'Myapp';
            $this->url = new Url();
            $this->parseUrlPathComponents();
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

        // Attempt 1: Look for Routes class in project and call routeResponder method
        $method = 'routeResponder';
        $controller = $this->project . '\\Routes';
        if (((class_exists($controller))) && (is_callable($controller . '::' . $method, true))) {
            $route = call_user_func(array(new $controller, $method), $this->url);
            // If we get a class name back, look for another routeRequest method within
            if ((is_string($route)) && (strpos($route, '::') === false) && (class_exists($this->project . '\\Controllers\\' . $route))) {
                $routeController = $this->project . '\\Controllers\\' . $route;
                $controllerClass = new $routeController;
                // Call routeResponder in the controller class
                $route = call_user_func(array($controllerClass, $method), $this->url);
                // If I get a partial string result, assume it's a method response
                if ((is_string($route)) && (strpos($route, '::') === false) && (is_callable(array($controllerClass, $route)))) {
                    return $this->callRoute($controllerClass, $route);
                }
            }
            // At this stage, we expect a string back in format $controller::$method
            if ((is_string($route)) && (strpos($route, '::') !== false)) {
                list($controller, $method) = explode('::', $this->project . '\\Controllers\\' . $route);
                if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
                    return $this->callRoute(new $controller, $method);
                }
            }
        }

        // Attempt 2: pointing to a controller with default route
        $path = $pathComponents;
        $method = 'routeDefault';
        $controller = $this->project . '\\Controllers\\' . (empty($path) ? 'Index' : implode('\\', $path));
        if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
            return $this->callRoute(new $controller, $method);
        }

        // Attempt 3: pointing to a specific route* method within a controller
        $path = $pathComponents;
        $method = 'route' . array_pop($path);
        $controller = $this->project . '\\Controllers\\' . (empty($path) ? 'Index' : implode('\\', $path));
        if ((class_exists($controller)) && (is_callable($controller . '::' . $method, true))) {
            return $this->callRoute(new $controller, $method);
        }

        // Can't parse
        // @todo Handle this better, with a 404 or exception screen
        throw new RouteNotFoundException($this->url);

    }

    /*
     * Calls the specified route method and injects parameters
     * @param $class controller class
     * @param $method string method name
     */
    private function callRoute($class, $method)
    {

        if (!is_callable(array($class, $method))) {
            throw new RouteNotFoundException($this->url);;
        }

        $reflection = new \ReflectionMethod($class, $method);
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
                    throw new Exception\UnknownAliasException($this->getParamClassName($param), get_class($class), $param->getDeclaringFunction()->name);
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
                    throw new Exception\ClassNotFoundException($alias, get_class($class), $param->getDeclaringFunction()->name);
                }
            }
            // If we get this far, we should have the class aliased and auto-loaded
            if ($paramClass instanceof \ReflectionClass) {
                // Instantiate parameter class and save to injection array
                $inject[] = new $paramClass->name();
            }
        }

        // Send the injected parameters into the identified method
        $reflection->invokeArgs($class, $inject);

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
