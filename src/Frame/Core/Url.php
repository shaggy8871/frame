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

        $pathComponents = parse_url($this->scheme . '://' . $this->host . $_SERVER['REQUEST_URI']);
        $pathSplit = explode('/', substr($pathComponents['path'], 1));

        // Determine the script filename so we can exclude it from the parsed path
        $scriptFilename = basename($_SERVER['SCRIPT_FILENAME']);

        $this->queryString = (isset($pathComponents['query']) ? $pathComponents['query'] : '');
        $this->pathComponents = [];

        $appFound = !in_array($scriptFilename, $pathSplit);
        foreach($pathSplit as $pathItem) {
            if ($appFound) {
                if ($pathItem) {
                    $this->pathComponents[] = $pathItem;
                }
            } else {
                if ($pathItem == $scriptFilename) {
                    $appFound = true;
                }
            }
        }

        $this->requestUri = '/' . implode('/', $this->pathComponents);

    }

    /*
     * Magic getter prevents class values from being overwritten
     */
    public function __get($property)
    {

        return $this->$property;

    }

}
