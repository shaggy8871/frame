<?php

namespace Frame\Core\Exception;

class UnknownAliasException extends \Exception
{

    public function __construct($alias, $declaringClass, $declaringMethod, $code = 0, Exception $previous = null) {
        parent::__construct('Could not find alias class for ' . $alias . ' as referenced in ' . $declaringClass . '::' . $declaringMethod, $code, $previous);
    }

}
