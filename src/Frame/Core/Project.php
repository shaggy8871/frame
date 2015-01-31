<?php

namespace Frame\Core;

class Project
{

    public $ns;
    public $path;
    public $debugMode;
    public $config;

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

}
