<?php

namespace Frame\Response;

class Html extends Foundation implements ResponseInterface
{

    protected $contentType = 'text/html';

    public function render($params = null)
    {

        $params = ($params ?: $this->viewParams);

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            header('Content-Type: ' . $this->contentType);
        }

        echo $params;

    }

}
