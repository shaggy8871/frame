<?php

namespace Frame\Request;

use Frame\Core\Exception\UnknownPropertyException;

abstract class Foundation
{

    protected $context;
    protected $type;
    protected $flash;

    public function __construct(\Frame\Core\Context $context)
    {

        $this->context = $context;

        // Attempt to guess the type
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->type = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));
        } else
        if (php_sapi_name() == 'cli') {
            $this->type = 'Args';
        } else {
            $this->type = 'Unknown';
        }

        // Remove flash from session if available
        if (isset($_SESSION['FRAME.flash'])) {
            $this->flash = json_decode($_SESSION['FRAME.flash']);
            unset($_SESSION['FRAME.flash']);
        }

    }

    /*
     * Returns the type of request
     */
    public function getType()
    {

        return $this->type;

    }

    /*
     * Returns the saved context
     */
    public function getContext()
    {

        return $this->context;

    }

    /*
     * Handy accessor to get the URL straight from the context
     */
    public function getUrl()
    {

        return $this->context->getUrl();

    }

    /*
     * Look up the saved Flash value if available
     */
    public function getFlash($key)
    {

        return (($this->flash) && (property_exists($this->flash, $key)) ? $this->flash->$key : null);

    }

    /*
    * Return all public and protected values
    */
    public function __get($property)
    {

        if (!property_exists($this, $property)) {
            throw new UnknownPropertyException($property, __CLASS__);
        }

        $reflect = new \ReflectionProperty($this, $property);
        if (!$reflect->isPrivate()) {
            return $this->$property;
        }

    }

    /*
    * Returns true if the property is set
    */
    public function __isset($property)
    {

        if (!property_exists($this, $property)) {
            throw new UnknownPropertyException($property, __CLASS__);
        }

        $reflect = new \ReflectionProperty($this, $property);
        if ((!$reflect->isPrivate()) && ($this->property)) {
            return true;
        }

    }

}
