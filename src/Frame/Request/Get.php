<?php

namespace Frame\Request;

class Get extends Foundation implements RequestInterface
{

    protected $get = [];

    /*
     * GET values are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        parent::__construct($context);

        $this->type = 'Get';
        $this->get = $_GET;

    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->get;

    }

    /*
     * Magic getter method maps requests to the protected $get property
     */
    public function __get($property)
    {

        return (isset($this->get[$property]) ? $this->get[$property] : null);

    }

}
