<?php

/*
 * Example helper class extends and modifies Get
 */

namespace Myapp\Controllers\Helpers;

class TestHelper extends \Frame\Request\Get
{

    public function __construct()
    {

        parent::__construct();

        foreach($this->toArray() as $prop => $val) {
            $this->$prop = '*'.$val;
        }

    }

}
