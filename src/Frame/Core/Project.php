<?php

namespace Frame\Core;

class Project
{

    protected $ns;
    protected $path;
    protected $debugMode;
    protected $config;
    protected $services;

    public function __construct($ns, $path, $debugMode)
    {

        $configClass = $ns . '\\Config';

        $this->ns = $ns;
        $this->path = ($path ? realpath($path) . '/' : '') . str_replace('\\', '/', $ns);
        $this->debugMode = $debugMode;
        // Do we have a configuration class?
        $this->config = (class_exists($configClass) ? new $configClass($this) : new \stdClass());

    }

    /*
     * Handy accessor to saved project namespace
     */
    public function getNamespace()
    {

        return $this->ns;

    }

    /*
     * Handy accessor to saved project path
     */
    public function getPath()
    {

        return $this->path;

    }

    /*
     * Handy accessor to saved debug mode value
     */
    public function getDebugMode()
    {

        return $this->debugMode;

    }

    /*
     * Handy accessor to saved config class
     */
    public function getConfig()
    {

        return $this->config;

    }

    /*
     * Add a public service to the project
     */
    public function addService($name, $object)
    {

        $this->services[$name] = $object;

    }

    /*
     * Returns the public service
     */
    public function getService($name)
    {

        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

    }

    /*
     * Magic getter method maps requests to some protected properties
     */
    public function __get($property)
    {

        return (in_array($property, ['ns', 'path', 'debugMode', 'config']) ?
            $this->$property : null);

    }

    /*
     * Returns true if some protected properties exist
     */
    public function __isset($property)
    {

        return (in_array($property, ['ns', 'path', 'debugMode', 'config']) ?
            property_exists($this, $property) : null);

    }

}
