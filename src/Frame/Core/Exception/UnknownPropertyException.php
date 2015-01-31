<?php

namespace Frame\Core\Exception;

class UnknownPropertyException extends \Exception
{

    public function __construct($property, $class, $code = 0, \Exception $previous = null) {
        parent::__construct('Unknown property ' . $property . ' in ' . $class, $code, $previous);
    }

}
