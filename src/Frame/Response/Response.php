<?php

namespace Frame\Response;

use Frame\Core\Context;

class Response extends Foundation implements ResponseInterface
{

    private $responseClass;

    /*
     * Use our own constructor, do not call the parent constructor so the
     * view directory is not set initially
     */
    public function __construct(Context $context)
    {

        $this->context = $context;

    }

    /*
     * Set the response class
     */
    public function setResponseClass($responseClass)
    {

        if ($responseClass instanceof ResponseInterface) {
            $this->responseClass = $responseClass;
        } else {
            throw new \Exception('Class ' . get_class($responseClass) . ' does not exist, cannot be set as response type');
        }

        return $this; // allow for chaining

    }

    /*
     * Overridden response class method
     */
    public function setView(array $view)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use $response->setResponseClass() to indicate.');
        }

        $this->responseClass->setView($view);

        return $this; // allow for chaining

    }

    /*
     * Overridden response class method
     */
    public function setViewDir($dir)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use $response->setResponseClass() to indicate.');
        }

        $this->responseClass->setViewDir($dir);

        return $this; // allow for chaining

    }

    /*
     * Overridden response class method
     */
    public function setViewParams($params)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use $response->setResponseClass() to indicate.');
        }

        $this->responseClass->setViewParams($params);

        return $this; // allow for chaining

    }

    /*
     * Overridden response class method
     */
    public function setStatusCode($statusCode)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use $response->setResponseClass() to indicate.');
        }

        $this->responseClass->setStatusCode($statusCode);

        return $this; // allow for chaining

    }

    /*
     * Overridden response class method
     */
    public function setContentType($contentType)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use $response->setResponseClass() to indicate.');
        }

        $this->responseClass->setContentType($contentType);

        return $this; // allow for chaining

    }

    /*
     * Overridden response class method
     */
    public function render($params = null)
    {

        if (!$this->responseClass) {
            throw new \Exception('No Response type defined. Use $response->setResponseClass() to indicate.');
        }

        $this->responseClass->render($params);

    }

}
