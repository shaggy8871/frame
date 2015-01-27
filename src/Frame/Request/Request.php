<?php

namespace Frame\Request;

class Request
{

    protected $context;
    protected $type;
    protected $flash;

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

        // Remove flash from session if available
        if (isset($_SESSION['FRAME.flash'])) {
            $this->flash = json_decode($_SESSION['FRAME.flash']);
            unset($_SESSION['FRAME.flash']);
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
     * Look up the saved Flash value if available
     */
    public function getFlash($key)
    {

        return (isset($this->flash->$key) ? $this->flash->$key : null);

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
