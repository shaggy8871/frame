<?php

namespace Frame\Core\Exception;

use Frame\Core\Url;
use Frame\Core\Project;

class RouteNotFoundException extends \Exception
{

    protected $url;
    protected $project;

    public function __construct(Url $url, Project $project, $code = 0, \Exception $previous = null) {

        $this->url = $url;
        $this->project = $project;

        parent::__construct('Could not find route for request uri ' . $url->requestUri, $code, $previous);

    }

    /*
     * Return the URL that caused the error
     */
    public function getUrl()
    {

        return $this->url;

    }

    /*
     * Return the project
     */
    public function getProject()
    {

        return $this->project;

    }

}
