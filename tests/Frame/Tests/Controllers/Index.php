<?php

namespace Frame\Tests\Controllers;

class Index extends Controller
{

    public function routeDefault()
    {

        return "RouteDefault";

    }

    public function routeJsonResponse(Get $request, Json $response)
    {

        return array('json' => true);

    }

    public function routeTwigResponse(Get $request, Twig $response)
    {

        return array('twig' => 'Okay');

    }

    public function routeProducts()
    {

        throw new Exception('Routing error, should route to Products::routeDefault');

    }

}
