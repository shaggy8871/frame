<?php

namespace Frame\Request;

class Get extends Request
{

    /*
     * GET values are simply stored as object properties - unsanitized!
     */
    public function __construct()
    {

        foreach ($_GET as $key => $value) {
            $this->$key = $value;
        }

    }

}
