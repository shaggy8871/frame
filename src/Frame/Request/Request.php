<?php

namespace Frame\Request;

class Request
{

    protected $context;
    protected $type;

    public function __construct(\Frame\Core\Context $context)
    {

        $this->context = $context;

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

        return new Get($this->context);

    }

    /*
     * Returns a POST request object
     */
    public function post()
    {

        return new Post($this->context);

    }

    /*
     * Returns an Args request object
     */
    public function args()
    {

        return new Args($this->context);

    }

    /*
     * Returns route parameters
     */
    public function routeParams()
    {

        return RouteParams::createFromRequest($this);

    }

    /*
     * Returns the type of request
     */
    public function getType()
    {

        return $this->type;

    }

    /*
     * Returns the saved context
     */
    public function getContext()
    {

        return $this->context;

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
