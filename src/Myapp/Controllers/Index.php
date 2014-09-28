<?php

namespace Myapp\Controllers;

class Index
{

    public function routeDefault(Get $in, Html $out)
    {

        $out->render("Default home page");

    }

    public function routeProduct(Helpers\TestHelper $in, Html $out)
    {

        $out->render(__METHOD__);

    }

}
