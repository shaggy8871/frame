<?php

namespace Frame\Response;

/*
 * For CLI sapi only
 */

class StdOut implements ResponseInterface
{

    /*
     * Render must send through a string
     */
    public function render($values = null)
    {

        if (!is_string($values)) {
            throw new InvalidResponseException('StdOut response value must be a string');
        }

        fwrite(STDOUT, $values);

    }

}
