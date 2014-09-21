<?php

namespace Frame\Core;

class Router
{

    // Order of namespace aliases
    private $paramAliases = [
        'Frame\\Request',
        'Frame\\Response'
    ];

    public function __construct()
    {

        // Hard-coded for now...
        $a = new \Controllers\Products();
        $m = 'routeDefault';
        $this->callRoute($a, $m);

    }

    protected function callRoute($class, $method)
    {

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
        call_user_func_array(array($class, $method), $inject);

    }

    /*
     * Copied and modified from http://php.net/manual/en/reflectionparameter.getclass.php#108620
     */
    protected function getParamClassName(\ReflectionParameter $param)
    {

        preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', $param->__toString(), $matches);
        return isset($matches[1]) ? $matches[1] : null;

    }

    /*
     * Creates a namespace alias only if it doesn't already exist
     */
    protected function createAlias($from, $to)
    {

        if ((class_exists($from)) && (!class_exists($to))) {
            class_alias($from, $to);
        }

    }

}
