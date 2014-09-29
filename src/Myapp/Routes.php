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
            return '\\Myapp\\Models\\Test1::getSomething'; // Should route to a model class instead
        }

    }

}
