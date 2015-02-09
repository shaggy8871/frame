<?php

namespace Frame\Request;

class Request extends Foundation implements RequestInterface
{

    const TYPE_GET = 'Get';
    const TYPE_POST = 'Post';
    const TYPE_PUT = 'Put';
    const TYPE_DELETE = 'Delete';
    const TYPE_OPTIONS = 'Options';
    const TYPE_PATCH = 'Patch';

    protected $get;
    protected $post;
    protected $put;
    protected $args;
    protected $routeParams;

    /*
     * Returns a GET request object
     */
    public function get()
    {

        if (!$this->get) {
            $this->get = new Get($this->context);
        }

        return $this->get;

    }

    /*
     * Returns a POST request object
     */
    public function post()
    {

        if (!$this->post) {
            $this->post = new Post($this->context);
        }

        return $this->post;

    }

    /*
     * Returns a PUT request object
     */
    public function put()
    {

        if (!$this->put) {
            $this->put = new Put($this->context);
        }

        return $this->put;

    }

    /*
     * Returns an Args request object
     */
    public function args()
    {

        if (!$this->args) {
            $this->args = new Args($this->context);
        }

        return $this->args;

    }

    /*
     * Returns route parameters
     */
    public function routeParams()
    {

        if (!$this->routeParams) {
            $this->routeParams = RouteParams::createFromRequest($this);
        }

        return $this->routeParams;

    }

    /**
     * @return Boolean if this request is a GET
     */
    public function isGet()
    {

        return $this->type == self::TYPE_GET;

    }

    /**
     * @return boolean if this request is a POST
     */
    public function isPost()
    {

        return $this->type == self::TYPE_POST;

    }

    /**
     * @return boolean if this request is a PUT
     */
    public function isPut()
    {

        return $this->type == self::TYPE_PUT;

    }

    /**
     * @return boolean if this request is a DELETE
     */
    public function isDelete()
    {

        return $this->type == self::TYPE_DELETE;

    }

    /**
     * @return boolean if this request is a OPTIONS
     */
    public function isOptions()
    {

        return $this->type == self::TYPE_OPTIONS;

    }

    /**
     * @return boolean if this request is a PATCH
     */
    public function isPatch()
    {

        return $this->type == self::TYPE_PATCH;

    }

    /*
     * To meet contract requirements
     */
    public function toArray()
    {

        return [];

    }

}
