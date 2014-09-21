<?php

namespace Controllers;

class Products
{

    public function routeDefault(Get $in, Json $out, \Models\Something $else, \Models\Test1 $another)
    {

        //$out->setType('json'); // only necessary if $out is defined as Response
        $out->render(array(
            array(
                'a' => 'b',
                'c' => 'd'
            )
        ));

    }

}
