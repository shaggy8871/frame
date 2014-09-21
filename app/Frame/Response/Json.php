<?php

namespace Frame\Response;

class Json implements ResponseInterface
{

    private $contentType = 'application/json';

    public function render(array $values = null)
    {

        if (!headers_sent()) {
            header('Content-Type: ' . $this->contentType);
        }

        echo json_encode($values);

    }

    /*
     * Set the content type to something other than the default
     */
    public function setContentType($contentType)
    {

        $this->contentType = $contentType;

    }

}
