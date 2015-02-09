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

}
