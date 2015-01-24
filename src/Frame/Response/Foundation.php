<?php

namespace Frame\Response;

use Frame\Core\Project;
use Frame\Response\Exception\ReverseRouteLookupException;
use Frame\Response\Exception\ResponseConfigException;

abstract class Foundation
{

    protected $project;
    protected $viewDir = '';
    protected $viewFilename = '';
    protected $viewParams = [];
    protected $statusCode = 200;
    protected $contentType = 'text/html';
    protected $flash;

    public function __construct(Project $project = null)
    {

        $this->project = ($project ? $project : new Project('', '', new \stdClass()));

        // Attempt to auto-detect the view directory path
        if ($this->project->path) {
            $this->setViewDir($this->project->path . '/Views');
        }

        // Remove flash from session if available
        if (isset($_SESSION['FRAME.flash'])) {
            $this->flash = json_decode($_SESSION['FRAME.flash']);
            unset($_SESSION['FRAME.flash']);
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
     * Sends a Flash message that disappears on the next page view
     */
    public function flash($key, $message)
    {

        if (session_status() == PHP_SESSION_NONE) {
            throw new ResponseConfigException("Flash message requires sessions to be enabled.");
        }

        if (isset($_SESSION['FRAME.flash'])) {
            $flash = json_decode($_SESSION['FRAME.flash']);
        } else {
            $flash = [];
        }

        if (is_array($flash)) {
            $flash[$key] = $message;
        } else {
            $flash = [
                $key => $message
            ];
        }

        $_SESSION['FRAME.flash'] = json_encode($flash);

    }

    /*
     * Look up the saved Flash value if available
     */
    public function getFlash($key)
    {

        return (isset($this->flash[$key]) ? $this->flash[$key] : null);

    }

    /*
     * Redirect to the specified URL
     */
    public function redirect($url, $statusCode = 302)
    {

        header(sprintf("Location: %s", $url), true, $statusCode);
        die(); // make sure we stop

    }

    /*
     * Looks up the canonical URL for a method if it's available via DocBlock
     * The $method parameter should be of type callable, which is a string of format
     * class::methodName or an array of [class, methodName]
     */
    public function urlFor(callable $mixed, array $params = null)
    {

        try {
            if (is_array($mixed)) {
                $reflection = new \ReflectionMethod($mixed[0], $mixed[1]);
            } else {
                $reflection = new \ReflectionMethod($mixed);
            }
        } catch (\ReflectionException $e) {
            throw new ReverseRouteLookupException("Parameter passed to the urlFor method is not callable");
        }

        $doc = $reflection->getDocComment();
        if (!$doc) {
            throw new ReverseRouteLookupException("The urlFor method expects a DocBlock with @canonical parameter above " . $reflection->getDeclaringClass()->getName() . "::" . $reflection->getName());
        }

        // Split into components (should be moved into helper class)
        $annotations = array();
        if (preg_match_all('#@(.*?)\n#s', $doc, $components)) {
        	foreach($components[1] as $annotation) {
        	   list($key, $val) = preg_split('/[ :]+/', $annotation, 2);
        	   $annotations[$key] = $val;
        	}
        }

        if (!isset($annotations['canonical'])) {
            throw new ReverseRouteLookupException("The method " . $reflection->getDeclaringClass()->getName() . "::" . $reflection->getName() . " has no @canonical DocBlock configured.");
        }

        $canonical = $annotations['canonical'];

        // Replace in parameters
        if ($params) {
            foreach($params as $name => $param) {
                $canonical = str_replace(':' . $name, $param, $canonical);
            }
        }

        return $canonical;

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
