<?php

namespace Frame\Response;

abstract class Foundation
{

    protected $contentType;
    protected $viewFilename;
    protected $viewParams;

    /*
     * Set the content type to something other than the default
     */
    public function setContentType($contentType)
    {

        $this->contentType = $contentType;

    }

    /*
     * Change the view filename and path
     */
    public function setViewFilename($filename)
    {

        $this->viewFilename = $filename;

    }

    /*
     * Set the view parameters prior to rendering
     */
    public function setViewParams($params)
    {

        $this->viewParams = $params;

    }

    /*
     * Return all public and protected values
     */
    public function __get($property)
    {

        $reflect = new \ReflectionProperty($this, $property);
        if (!$reflect->isPrivate()) {
            return $this->$property;
        }

    }

    /*
     * If the response class itself is output, call the render method automatically
     */
    public function __toString()
    {

        if (method_exists($this, 'render')) {
            $this->render();
        }

    }

}
