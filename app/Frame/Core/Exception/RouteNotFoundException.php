<?php

namespace Frame\Core\Exception;

class RouteNotFoundException extends \Exception
{

    public function __construct(\Frame\Core\Url $url, $code = 0, Exception $previous = null) {
        parent::__construct('Could not find route for request uri ' . $url->requestUri, $code, $previous);
    }

}
