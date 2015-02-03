<?php

namespace Frame\Tests\Controllers;

use Frame\Core\Controller;
use Frame\Request\Get;
use Frame\Request\RouteParams;
use Frame\Response\Html;
use Frame\Response\Json;
use Frame\Response\Jsonp;
use Frame\Response\Twig;

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

    /**
     * @canonical /urlParamsRequest/:id(/:slug)
     */
    public function routeUrlParamsRequest(RouteParams $request, Html $response)
    {

        echo json_encode(['id' => $request->id, 'slug' => $request->slug]);

    }

    public function routeUrlFor(Get $request, Html $response)
    {

        echo $response->urlFor([$this, 'routeUrlDestination'], [
            'var' => 'val'
        ]);

    }

    public function routeUrlForHome(Get $request, Html $response)
    {

        echo $response->urlFor('routeDefault');

    }

    public function routeUrlForAutodetect1(Get $request, Html $response)
    {

        // Route to the local method
        echo $response->urlFor('routeUrlDestinationAutodetect');

    }

    public function routeUrlForAutodetect2(Get $request, Html $response)
    {

        // Route to the method within Products controller
        echo $response->urlFor('Products::routeUrlDestinationAutodetect');

    }

    public function routeUrlForFallback1(Get $request, Html $response)
    {

        // Method name only, should assume current class
        echo $response->urlFor('routeUrlParamsRequest', [
            'id' => '123',
            'slug' => 'slugger'
        ]);

    }

    public function routeUrlForFallback2(Get $request, Html $response)
    {

        // Partial class name, should automatically namespace to current project
        echo $response->urlFor('Products::routeUrlDestination', [
            'var' => 'val'
        ]);

    }

    public function routeUrlForSuffix(Get $request, Html $response)
    {

        // Test suffix on URL
        echo $response->urlFor('routeUrlDestinationSuffix', [
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

    /**
     * @canonical /urlDestination/:var/suffix
     */
    public function routeUrlDestinationSuffix()
    {

        // Used only for routeUrlFor tests

    }

    public function routeUrlDestinationAutodetect()
    {

        // Used only for routeUrlForAutodetect tests

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
