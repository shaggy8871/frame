<?php

namespace Frame\Request;

class Request extends Foundation
{

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

}
