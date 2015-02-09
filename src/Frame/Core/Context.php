<?php

/*
 * The Context object gets passed to the Request or Response class with information
 * about how it is called
 */

namespace Frame\Core;

use Frame\Core\Exception\UnknownPropertyException;

class Context
{

    protected $project;
    protected $url;
    protected $caller;

    public function __construct(Project $project = null, Url $url = null, Caller $caller = null)
    {

        $this->project = $project;
        $this->url = $url;
        $this->caller = $caller;

    }

    /*
     * Return the project
     */
    public function getProject()
    {

        return $this->project;

    }

    /*
     * Return the parsed url class that we're using
     */
    public function getUrl()
    {

        return $this->url;

    }

    /*
     * Return the caller information
     */
    public function getCaller()
    {

        return $this->caller;

    }

    /*
     * Magic getter prevents class values from being overwritten
     */
    public function __get($property)
    {

        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new UnknownPropertyException($property, __CLASS__);
        }

    }

}
