<?php

namespace Frame\Response;

class Response implements ResponseInterface
{

    private $responseClass;

    /*
     * Change the response class during runtime
     */
    public function setType($type)
    {

        $responseClass = __NAMESPACE__ . '\\' . ucfirst($type);
        if (class_exists($responseClass)) {
            $this->responseClass = new $responseClass;
        } else {
            throw new \Exception('Class ' . $responseClass . ' does not exist, cannot be set as response type');
        }

    }

    /*
     * Call the defined response class
     */
    public function render(array $values = null)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use Response \$out->setType to indicate.');
        }

        $this->responseClass->render($values);

    }

}
