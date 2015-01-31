<?php

namespace Frame\Core;

/*
 * The base Url class stores the URL in workable components
 */

class Url
{

    protected $requestMethod;
    protected $requestUri;
    protected $rootUri;
    protected $scheme;
    protected $host;
    protected $port;
    protected $pathComponents;
    protected $queryString;

    public function __construct(array $urlComponents)
    {

        if (isset($urlComponents['requestMethod'])) {
            $this->requestMethod = $urlComponents['requestMethod'];
        }
        if (isset($urlComponents['requestUri'])) {
            $this->requestUri = $urlComponents['requestUri'];
        }
        if (isset($urlComponents['rootUri'])) {
            $this->rootUri = $urlComponents['rootUri'];
        }
        if (isset($urlComponents['scheme'])) {
            $this->scheme = $urlComponents['scheme'];
        }
        if (isset($urlComponents['host'])) {
            $this->host = $urlComponents['host'];
        }
        if (isset($urlComponents['port'])) {
            $this->port = $urlComponents['port'];
        }
        if (isset($urlComponents['pathComponents'])) {
            $this->pathComponents = $urlComponents['pathComponents'];
        }
        if (isset($urlComponents['queryString'])) {
            $this->queryString = $urlComponents['queryString'];
        }

    }

    /*
     * Magic getter prevents class values from being overwritten
     */
    public function __get($property)
    {

        return $this->$property;

    }

}
