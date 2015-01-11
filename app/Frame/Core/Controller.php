<?php

namespace Frame\Core;

abstract class Controller
{

    protected $project;

    public function __construct(Project $project)
    {

        $this->project = $project;

    }

}
