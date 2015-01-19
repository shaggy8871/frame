<?php

namespace Frame\Request;

class Request
{

    protected $type;

    public function __construct()
    {

        // Attempt to guess the type

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            switch ($requestMethod) {
                case 'POST':
                    $this->type = 'Post';
                    break;
                case 'GET':
                    $this->type = 'Get';
                    break;
                default:
                    $this->type = ucfirst($requestMethod);
                    break;
            }
        } else
        if (php_sapi_name() == 'cli') {
            $this->type = 'Args';
        } else {
            $this->type = 'Unknown';
        }

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
