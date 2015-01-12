<?php

namespace Frame\Response;

use Frame\Core\Project;
use Frame\Response\Exception\ResponseConfigException;

abstract class Foundation
{

    protected $project;
    protected $viewDir = '';
    protected $viewFilename = '';
    protected $viewParams = [];
    protected $statusCode = 200;
    protected $contentType = 'text/html';

    public function __construct(Project $project = null)
    {

        $this->project = ($project ? $project : new Project('', '', new \stdClass()));

        // Attempt to auto-detect the view directory path
        if ($this->project->path) {
            $this->setViewDir($this->project->path . '/Views');
        }

    }

    /*
     * Set defaults for the response class post instantiation
     */
    public function setDefaults(array $defaults)
    {

        if (isset($defaults['project'])) {
            $this->setProject($defaults['project']);
        }
        if (isset($defaults['viewDir'])) {
            $this->setViewDir($defaults['viewDir']);
        }
        if (isset($defaults['viewFilename'])) {
            $this->setViewFilename($defaults['viewFilename']);
        }

        if (is_array($defaults['view'])) {
            $this->setView($defaults['view']);
        }

    }

    /*
    * Set the project
    */
    public function setProject($project)
    {

        $this->project = $project;

        return $this; // allow for chaining

    }

    /*
    * Change the view filename and base directory
    */
    public function setView(array $view)
    {

        if ((isset($view['dir'])) && (isset($view['filename']))) {
            $this->viewDir = $view['dir'];
            $this->viewFilename = $view['filename'];
        } else {
            throw new ResponseConfigException("Parameter 1 of setView must contain keys 'dir' and 'filename'");
        }

        return $this; // allow for chaining

    }

    /*
    * Change the view base directory
    */
    public function setViewDir($dir)
    {

        $this->viewDir = $dir;

        return $this; // allow for chaining

    }

    /*
     * Change the view filename and path
     */
    public function setViewFilename($filename)
    {

        $this->viewFilename = $filename;

        return $this; // allow for chaining

    }

    /*
     * Set the view parameters prior to rendering
     */
    public function setViewParams($params)
    {

        $this->viewParams = $params;

        return $this; // allow for chaining

    }

    /*
     * Set the response status code
     */
    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;

        return $this; // allow for chaining

    }

    /*
    * Set the content type to something other than the default
    */
    public function setContentType($contentType)
    {

        $this->contentType = $contentType;

        return $this; // allow for chaining

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
