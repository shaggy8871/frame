<?php

namespace Frame\Response;

class Xml implements ResponseInterface
{

    /*
     * @todo...
     */
    public function render($values = null)
    {

        if (!is_array($values)) {
            throw new InvalidResponseException('Xml response value must be an array');
        }

    }

}
