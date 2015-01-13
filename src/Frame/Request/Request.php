<?php

namespace Frame\Request;

class Request
{

    protected $type;

    public function __construct()
    {

        $this->type = $_SERVER['REQUEST_METHOD'];

    }

    /*
     * Returns a GET request object
     */
    public function get()
    {

        return new Get();

    }

    /*
    * Returns a POST request object
    */
    public function post()
    {

        return new Post();

    }

    /*
    * Returns an Args request object
    */
    public function args()
    {

        return new Args();

    }

    /*
    * Return all public and protected values
    */
    public function __get($property)
    {

        $reflect = new \ReflectionProperty($this, $property);
        if (!$reflect->isPrivate()) {
            return $this->$property;
        }

    }

}
