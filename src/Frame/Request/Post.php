<?php

namespace Frame\Request;

class Post extends Request
{

    protected $post = array();

    /*
     * POST values are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        $this->type = 'Post';
        $this->post = $_POST;

        parent::__construct($context);

    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->post;

    }

    /*
     * Magic getter method maps requests to the protected $post property
     */
    public function __get($property)
    {

        return (isset($this->post[$property]) ? $this->post[$property] : null);

    }

}
