<?php

namespace Frame\Response;

class Html implements ResponseInterface
{

    public function render($values = null)
    {

        print_r($values);

    }

}
