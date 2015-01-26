<?php

namespace Frame\Tests\Controllers;

use Frame\Core\Controller;

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

    public function routeJsonpResponse(Get $request, Jsonp $response)
    {

        $response->setCallback('jsonp');
        return array('jsonp' => true);

    }

    public function routeTwigResponse(Get $request, Twig $response)
    {

        return array('twig' => 'Okay');

    }

    public function routeUrlFor(Get $request, Html $response)
    {

        echo $response->urlFor([$this, 'routeUrlDestination'], [
            'var' => 'val'
        ]);

    }

    public function routeUrlForFallback1(Get $request, Html $response)
    {

        // Method name only, should assume current class
        echo $response->urlFor('routeUrlDestination', [
            'var' => 'val'
        ]);

    }

    public function routeUrlForFallback2(Get $request, Html $response)
    {

        // Partial class name, should automatically namespace to current project
        echo $response->urlFor('Products::routeUrlDestination', [
            'var' => 'val'
        ]);

    }

    public function routeTwigUrlFor(Get $request, Twig $response)
    {

        return array();

    }

    /**
     * @canonical /urlDestination/:var
     */
    public function routeUrlDestination()
    {

        // Used only for routeUrlFor tests

    }

    public function routeFlash(Get $request, Html $response)
    {

        $response->flash('with', 'flash');

        echo $_SESSION['FRAME.flash'];

    }

    public function routeRedirect(Get $request, Html $response)
    {

        $response->redirect('/');

    }

    public function routeProducts()
    {

        throw new Exception('Routing error, should route to Products::routeDefault');

    }

}
