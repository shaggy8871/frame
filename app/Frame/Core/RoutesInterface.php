<?php

namespace Frame\Core;

interface RoutesInterface
{

    /*
     * The routeResponder method must respond with either of the following:
     * 1. A controller class name
     * 2. A callable method within a controller class
     * 3. False to indicate that no route was found
     */
    function routeResponder(Url $url);

}
