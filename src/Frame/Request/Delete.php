<?php

namespace Frame\Request;

class Delete extends Foundation implements RequestInterface
{

    protected $delete = [];

    /*
     * DELETE values are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        $this->type = 'Delete';
        parse_str(file_get_contents("php://input"), $this->delete);

        parent::__construct($context);

    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->delete;

    }

    /*
     * Magic getter method maps requests to the protected $delete property
     */
    public function __get($property)
    {

        return (isset($this->delete[$property]) ? $this->delete[$property] : null);

    }

}
