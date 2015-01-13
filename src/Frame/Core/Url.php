<?php

namespace Frame\Core;

/*
 * The base Url class splits the URL into workable components
 */

class Url
{

    protected $requestMethod;
    protected $requestUri;
    protected $scheme;
    protected $host;
    protected $port;
    protected $pathComponents;
    protected $queryString;

    public function __construct()
    {

        // Basic lookup
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $this->host = $_SERVER['HTTP_HOST'];
        $this->port = $_SERVER['SERVER_PORT'];

        // Determine the script filename so we can exclude it from the parsed path
        $scriptFilename = basename($_SERVER['SCRIPT_FILENAME']);
        // Determine the correct request Uri
        $requestUri =
            (isset($_SERVER['FRAME_REQUEST_URI']) ? $_SERVER['FRAME_REQUEST_URI'] :
            (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI']
        ));

        $pathComponents = parse_url($this->scheme . '://' . $this->host . $requestUri . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));

        $this->requestUri = $requestUri;
        $this->pathComponents = explode('/', substr($pathComponents['path'], 1));
        $this->queryString = (isset($pathComponents['query']) ? $pathComponents['query'] : '');

    }

    /*
     * Magic getter prevents class values from being overwritten
     */
    public function __get($property)
    {

        return $this->$property;

    }

}
