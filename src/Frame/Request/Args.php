<?php

namespace Frame\Request;

/*
 * Get command line arguments
 */

class Args extends Request
{

    private $args = array();

    /*
     * Console arguments are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Router $router)
    {

        foreach ($GLOBALS['argv'] as $key => $value) {
            if ($key) {
                $this->args['arg' . $key] = $value;
            }
        }

        parent::_construct($router);

    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->args;

    }

    /*
     * Magic getter method maps requests to the private $args property
     */
    public function __get($property)
    {

        return (isset($this->args[$property]) ? $this->args[$property] : null);

    }

}
