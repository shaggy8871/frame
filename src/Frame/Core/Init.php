<?php

namespace Frame\Core;

use Frame\Core\Exception\ConfigException;
use Frame\Core\Exception\RouteNotFoundException;
use Frame\Response\Phtml;

class Init
{

    protected $projects;
    protected $onRouteNotFound;

    public function __construct(array $projects = array())
    {

        // Save projects
        $this->projects = $projects;

    }

    /*
     * Call a function if we are unable to route
     */
    public function onRouteNotFound(callable $callback)
    {

        $this->onRouteNotFound = $callback;

    }

    /*
     * Run the project
     */
    public function run()
    {

        // Initialize router
        try {

            if (php_sapi_name() == 'cli') {
                // Handle console apps
                // @todo: finish!
                $project = $this->getProjectFromArgs();
                $router = new Router($project);
            } else {
                // Handle web apps
                $url = new Url();
                $project = $this->getProjectFromUrl($url);
                $router = new Router($project, $url);
                $router->parseUrl();
            }

        } catch (RouteNotFoundException $e) {

            if ($this->onRouteNotFound) {
                // Call the user defined route not found handler
                call_user_func($this->onRouteNotFound, [
                    'project' => $e->getProject(),
                    'url' => $e->getUrl(),
                    'statusCode' => 404,
                    'message' => $e->getMessage()
                ]);
            } else {
                // Display a default error page
                $response = new Phtml($e->getProject());
                $response
                    ->setStatusCode(404)
                    ->setViewDir(__DIR__ . '/Scripts')
                    ->setViewFilename('error.phtml')
                    ->setViewParams([
                        'project' => $e->getProject(),
                        'url' => $e->getUrl(),
                        'statusCode' => 404,
                        'message' => $e->getMessage()
                    ])
                    ->render();
            }

        }

    }

    /*
     * Allow read access only
     */
    public function __get($property)
    {

        return $this->$property;

    }

    /*
    * Determine the project folder from the url hostname
    */
    private function getProjectFromUrl(Url $url)
    {

        if (array_key_exists($url->host, $this->projects)) {
            return $this->createProject($this->projects[$url->host]);
        } else {
            throw new ConfigException('Cannot determine project path from host ' . $url->host);
        }

    }

    /*
    * Determine the project folder from the first CLI argument
    */
    private function getProjectFromArgs()
    {

        if ((isset($GLOBALS['argv'])) && (count($GLOBALS['argv']) > 1) && (file_exists($GLOBALS['argv'][1]))) {
            return $this->createProject($GLOBALS['argv'][1]);
        } else {
            throw new ConfigException('Cannot determine project path from argument ' . $GLOBALS['argv'][1]);
        }

    }

    /*
    * Creates a project object
    */
    private function createProject($project)
    {

        if (is_a($project, 'Frame\\Core\\Project')) {
            return $project;
        } else
        if (is_array($project)) {
            $project = array_merge($project, array_fill(0, 2, false));
            list($ns, $path, $debugMode) = $project;
            if (!$ns) {
                throw new ConfigException("Project configuration must have a namespace assigned");
            }
        } else {
            list($ns, $path, $debugMode) = array($project, '', false);
        }

        return new Project($ns, $path, $debugMode);

    }

}
