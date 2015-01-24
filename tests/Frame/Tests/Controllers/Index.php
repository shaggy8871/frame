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

    /**
     * @canonical /urlDestination/:var
     */
    public function routeUrlDestination()
    {

        // Used only for routeUrlFor test

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
