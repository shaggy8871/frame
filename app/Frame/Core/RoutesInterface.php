<?php

namespace Frame\Core;

interface RoutesInterface
{

    /*
     * The routeResolver method must respond with either of the following:
     * 1. A controller class name if within a project's Routes class
     * 2. A method name or string in the format $controller::$method if within a controller class
     * 3. False or null to indicate that no route was found
     */
    function routeResolver(Url $url);

}
