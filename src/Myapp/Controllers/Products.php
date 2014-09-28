<?php

namespace Myapp\Controllers;

class Products
{

    /*
     * Handle controller specific routing queries
     */
    public function routeResponder(Url $url)
    {

        $found = preg_match("/^\/product1/", $url->requestUri, $matches);
        if ($found) {
            return 'Index::routeProduct'; // go to controller Index, method routeProduct
        }
        $found = preg_match("/^\/product2/", $url->requestUri, $matches);
        if ($found) {
            return 'anotherMethod'; // look in this controller for method anotherMethod
        }

    }

    public function routeDefault(Get $in, Json $out, \Myapp\Models\Something $else)
    {

        $out->render($in->toArray());

    }

    public function routeSubdir(Get $in)
    {

        echo "You are in subdir with get params " . print_r($in->toArray(), true);

    }

    public function routeDirect(Get $in, Html $out)
    {

        $out->render(__METHOD__);

    }

    public function anotherMethod(Get $in, Html $out)
    {

        $out->render(__METHOD__);

    }

}
