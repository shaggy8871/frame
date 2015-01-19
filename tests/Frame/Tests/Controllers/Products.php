<?php

namespace Frame\Tests\Controllers;

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
