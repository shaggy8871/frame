<?php

namespace Frame\Core\Exception;

class ClassNotFoundException extends \Exception
{

    public function __construct($class, $declaringClass, $declaringMethod, $code = 0, Exception $previous = null) {
        parent::__construct('Could not find alias class for ' . $class . ' as referenced in ' . $declaringClass . '::' . $declaringMethod, $code, $previous);
    }

}
