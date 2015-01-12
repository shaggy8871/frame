<?php

namespace Frame\Request;

class Post extends Request
{

    /*
     * POST values are simply stored as object properties - unsanitized!
     */
    public function __construct()
    {

        foreach ($_POST as $key => $value) {
            $this->$key = $value;
        }

    }

}
