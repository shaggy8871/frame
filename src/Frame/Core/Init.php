<?php

namespace Frame\Core;

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
            $router = new Router($this);
        } catch (RouteNotFoundException $e) {
            if ($this->onRouteNotFound) {
                call_user_func($this->onRouteNotFound, [
                    'project' => $e->getProject(),
                    'url' => $e->getUrl(),
                    'statusCode' => 404,
                    'message' => $e->getMessage()
                ]);
            } else {
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

}
