<?php

namespace Frame\Core;

class Init
{

    protected $projects;

    public function __construct(array $projects = array())
    {

        $this->projects = $projects;

        // Initialize config
        // ...
        // Initialize router
        new Router($this);

    }

    /*
     * Allow read access only
     */
    public function __get($property)
    {

        return $this->$property;

    }

}
