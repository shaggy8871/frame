<?php

namespace Frame\Response;

class Html extends Foundation implements ResponseInterface
{

    protected $contentType = 'text/html';

    public function render($params = null)
    {

        $params = ($params ? $params : $this->viewParams);

        if (!headers_sent()) {
            header('Content-Type: ' . $this->contentType);
        }

        echo $params;

    }

}
