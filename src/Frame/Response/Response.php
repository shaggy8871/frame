<?php

namespace Frame\Response;

class Response extends Foundation implements ResponseInterface
{

    private $responseClass;

    /*
     * Change the response class during runtime
     */
    public function setType($type)
    {

        $responseClass = __NAMESPACE__ . '\\' . $type;
        if (class_exists($responseClass)) {
            $this->responseClass = new $responseClass;
        } else {
            throw new \Exception('Class ' . $responseClass . ' does not exist, cannot be set as response type');
        }

    }

    /*
     * Call the defined response class
     */
    public function render($params = null)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use Response \$response->setType() to indicate.');
        }

        $this->responseClass->render($params);

    }

}
