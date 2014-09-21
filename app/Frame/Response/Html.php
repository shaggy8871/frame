<?php

namespace Frame\Response;

class Html implements ResponseInterface
{

    public function render(array $values = null)
    {

        print_r($values);

    }

}
