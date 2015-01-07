<?php

namespace Myapp\Controllers;

class Products
{

    /*
     * Handle controller specific routing queries
     */
    public function routeResolver(Url $url)
    {

        $found = preg_match("/^\/products\/int/", $url->requestUri, $matches);
        if ($found) {
            return '\\Myapp\\Models\\Test1::getSomething';
        }
        $found = preg_match("/^\/product1/", $url->requestUri, $matches);
        if ($found) {
            return 'Index::routeProduct'; // go to controller Index, method routeProduct
        }
        $found = preg_match("/^\/product2/", $url->requestUri, $matches);
        if ($found) {
            return 'anotherMethod'; // look in this controller for method anotherMethod
        }
        $found = preg_match("/^\/product3/", $url->requestUri, $matches);
        if ($found) {
            return (function(Get $request, Html $response) {
                return 'I am inside a closure';
            });
        }
        $found = preg_match("/^\/product4/", $url->requestUri, $matches);
        if ($found) {
            return array($this, 'routeDefault');
        }

    }

    public function routeDefault(Get $request, Twig $response, \Myapp\Models\Something $else)
    {

        return array('this' => 'is', 'cool' => 'yeah?');

    }

    public function routeSubdir(Get $request)
    {

        return "You are in subdir with get params " . print_r($request->toArray(), true);

    }

    public function routeDirect(Get $request, Html $response)
    {

        $response->render(__METHOD__);

    }

    public function anotherMethod(Get $request, Json $response)
    {

        $response->setViewParams(array('this'));
        return $response;

    }

}
