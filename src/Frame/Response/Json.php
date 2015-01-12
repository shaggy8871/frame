<?php

namespace Frame\Response;

class Json extends Foundation implements ResponseInterface
{

    protected $contentType = 'application/json';

    /*
     * Render content in Json encoded format
     */
    public function render($params = null)
    {

        $params = ($params ?: $this->viewParams);

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            header('Content-Type: ' . $this->contentType);
        }

        echo json_encode($params);

    }

}
