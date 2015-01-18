<?php

namespace Frame\Tests;

/*
 * A URL simulator class that replaces the Frame\Core\Url class
 */

class UrlSim
{

    public $requestMethod;
    public $requestUri;
    public $scheme;
    public $host;
    public $port;
    public $pathComponents;
    public $queryString;

}
