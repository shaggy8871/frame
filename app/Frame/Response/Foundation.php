<?php

namespace Frame\Response;

abstract class Foundation
{

    protected $project;
    protected $viewFilename;
    protected $viewBaseDir;
    protected $viewParams;
    protected $contentType;

    /*
     * Set defaults for the response class post instantiation
     */
    public function setDefaults(array $defaults)
    {

        if (isset($defaults['project'])) {
            $this->setProject($defaults['project']);
        }
        if (isset($defaults['viewFilename'])) {
            $this->setViewFilename($defaults['viewFilename']);
        }
        if (isset($defaults['viewBaseDir'])) {
            $this->setViewBaseDir($defaults['viewBaseDir']);
        }

    }

    /*
    * Set the project
    */
    public function setProject($project)
    {

        $this->project = $project;

    }

    /*
     * Change the view filename and path
     */
    public function setViewFilename($filename)
    {

        $this->viewFilename = $filename;

    }

    /*
    * Change the view base directory
    */
    public function setViewBaseDir($baseDir)
    {

        $this->viewBaseDir = $baseDir;

    }

    /*
     * Set the view parameters prior to rendering
     */
    public function setViewParams($params)
    {

        $this->viewParams = $params;

    }

    /*
    * Set the content type to something other than the default
    */
    public function setContentType($contentType)
    {

        $this->contentType = $contentType;

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
