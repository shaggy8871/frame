<?php

namespace Frame\Request;

class Put extends Foundation implements RequestInterface
{

    protected $put = [];

    /*
     * PUT values are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        parent::__construct($context);

        $this->type = 'Put';
        parse_str(file_get_contents("php://input"), $this->put);

    }

    /*
     * Optional method to set the input manually
     */
    public function setProps(array $properties)
    {

        $this->put = $properties;
        
    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->put;

    }

    /*
     * Magic getter method maps requests to the protected $put property
     */
    public function __get($property)
    {

        return (isset($this->put[$property]) ? $this->put[$property] : null);

    }

    /*
     * Magic isset method maps requests to the protected $put property
     */
    public function __isset($property)
    {

        return isset($this->put[$property]);

    }

}
