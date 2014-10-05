<?php

namespace Myapp\Controllers;

class Index
{

    public function routeDefault(Get $request)
    {

        return "Default home page";

    }

    public function routeProduct(Helpers\TestHelper $request, Html $response)
    {

        $response->render(__METHOD__);

    }

}
