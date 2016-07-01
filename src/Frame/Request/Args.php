<?php

namespace Frame\Request;

/*
 * Get command line arguments
 */

class Args extends Foundation implements RequestInterface
{

    private $args = [];

    /*
     * Console arguments are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        parent::__construct($context);

        $this->args = $GLOBALS['argv'];

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

    /*
     * Magic isset method maps requests to the protected $args property
     */
    public function __isset($property)
    {

        return isset($this->args[$property]);

    }

}
