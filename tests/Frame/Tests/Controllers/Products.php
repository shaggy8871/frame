<?php

namespace Frame\Tests\Controllers;

use Frame\Core\Controller;

class Products extends Controller
{

    public function routeDefault()
    {

        return "ProductsRouteDefault";

    }

    public function routeSubDir()
    {

        return "ProductsRouteSubDir";

    }

}