<?php

namespace Frame\Request;

class Request extends Foundation
{

    const TYPE_GET = 'Get';
    const TYPE_POST = 'Post';
    const TYPE_PUT = 'Put';
    const TYPE_DELETE = 'Delete';
    const TYPE_OPTIONS = 'Options';

    protected $get;
    protected $post;
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

}
