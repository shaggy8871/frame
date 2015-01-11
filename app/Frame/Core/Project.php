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

        $this->ns = $ns;
        $this->path = ($path ? getcwd() . '/' . $path : '');

        // Do we have a configuration class?
        $configClass = $ns . '\\Config';
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
