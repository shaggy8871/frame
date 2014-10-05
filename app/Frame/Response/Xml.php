<?php

namespace Frame\Response;

class Xml extends Foundation implements ResponseInterface
{

    protected $contentType = 'text/xml';

    /*
     * @todo...
     */
    public function render($params = null)
    {

        $params = ($params ? $params : $this->viewParams);

        if (!is_array($params)) {
            throw new InvalidResponseException('Xml response value must be an array');
        }

        if (!headers_sent()) {
            header('Content-Type: ' . $this->contentType);
        }

    }

}
