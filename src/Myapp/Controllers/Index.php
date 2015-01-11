<?php

namespace Myapp\Controllers;

class Index
{

    protected $project;

    /*
     * An alternative to using the Controller class
     */
    public function __construct(Project $project)
    {

        $this->project = $project;

    }

    public function routeDefault(Get $request)
    {

        return "Default home page";

    }

    public function routeProduct(Helpers\TestHelper $request, Html $response)
    {

        $response->render(__METHOD__);

    }

    public function routeFallback(Helpers\TestHelper $request, Html $response)
    {

        return 'I fell back' . var_dump($request);

    }

}
