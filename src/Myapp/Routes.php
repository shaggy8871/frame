<?php

namespace Myapp;

use \Frame\Core\RoutesInterface;

class Routes implements RoutesInterface
{

    public function routeResponder(Url $url)
    {

        $found = preg_match("/^\/product/", $url->requestUri, $matches);
        if ($found) {
            return 'Products'; // look in the Products controller
        }
        $found = preg_match("/^\/testing/", $url->requestUri, $matches);
        if ($found) {
            return 'Products::routeDirect'; // Go to a specific method
        }
        $found = preg_match("/^\/model/", $url->requestUri, $matches);
        if ($found) {
            return '\Myapp\Models\Test1::getSomething'; // Should route to a model class instead
        }
        $found = preg_match("/^\/closure/", $url->requestUri, $matches);
        if ($found) {
            return (function(Get $request, Json $response) {
                return 'I am inside a project closure';
            });
        }
        $found = preg_match("/^\/routemethod/", $url->requestUri, $matches);
        if ($found) {
            return array($this, 'routeMethod'); // point to the local method
        }

    }

    public function routeMethod(Get $request)
    {

        return "I am in routemethod";

    }

}
