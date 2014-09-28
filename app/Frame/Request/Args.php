<?php

namespace Frame\Request;

class Args extends Request
{

    private $args = array();

    /*
     * Console arguments are simply stored as object properties - unsanitized!
     */
    public function __construct()
    {

        foreach ($argv as $key => $value) {
            if ($key) {
                $this->args['arg' . $key] = $value;
            }
        }

    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->args;

    }

    /*
     * Magic getter method maps requests to the private $args property
     */
    public function __get($propery)
    {

        return (isset($this->args[$propery]) ? $this->args[$propery] : null);

    }

}
