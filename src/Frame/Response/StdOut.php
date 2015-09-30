<?php

namespace Frame\Response;

/*
 * For CLI sapi only
 */

class StdOut extends Foundation implements ResponseInterface
{

    /*
     * Render must send through a string
     */
    public function render($params = null)
    {

        $params = ($params != null ? $params : $this->viewParams);

        if (!is_string($params)) {
            throw new InvalidResponseException('StdOut response value must be a string');
        }

        fwrite(STDOUT, $params);

    }

}
