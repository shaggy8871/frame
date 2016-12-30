<?php

/*
 * The Caller object saves information about the controller/method that instantiated
 * the Request or Response class
 */

namespace Frame\Core;

use Frame\Core\Exception\UnknownPropertyException;

class Caller
{

    protected $controller;
    protected $method;
    protected $annotations;

    public function __construct($controller, $method, array $annotations = null)
    {

        $this->controller = $controller;
        $this->method = $method;
        $this->annotations = $annotations;

    }

    /*
     * Magic getter prevents class values from being overwritten
     */
    public function __get($property)
    {

        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new UnknownPropertyException($property, __CLASS__);
        }

    }

    /*
     * Activate getProperty() style method calls
     */
    public function __call($name, $args)
    {

        if (substr($name, 0, 3) == 'get') {
            $property = lcfirst(substr($name, 3));
            if (property_exists($this, $property)) {
                return $this->$property;
            } else {
                throw new Exception\UnknownPropertyException($property, __CLASS__);
            }
        }

    }

    /*
     * Returns true if the property exists
     */
    public function __isset($property)
    {

        return property_exists($this, $property);

    }

}
