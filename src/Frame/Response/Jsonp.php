<?php

namespace Frame\Response;

class Jsonp extends Foundation implements ResponseInterface
{

    protected $contentType = 'application/json';
    protected $callback = 'callback';

    /*
     * Render content in Jsonp encoded format
     */
    public function render($params = null)
    {

        $params = ($params != null ? $params : $this->viewParams);

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            header('Content-Type: ' . $this->contentType);
        }

        echo sprintf('%s(%s)', $this->callback, json_encode($params));

    }

    /*
     * Set another callback function name if not the default "callback"
     */
    public function setCallback($callback)
    {

        $this->callback = $callback;

    }

}
