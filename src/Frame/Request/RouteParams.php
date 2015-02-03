<?php

namespace Frame\Request;

use Frame\Core\Context;
use Frame\Core\Utils\Url;
use Frame\Request\Request;

class RouteParams extends Foundation
{

    protected $routeParams = array();

    /*
     * Route param values are simply stored as object properties - unsanitized!
     */
    public function __construct(Context $context)
    {

        $this->type = 'RouteParams';

        // Default approach to route param parsing
        $this->routeParams = explode('/', $context->getUrl()->requestUri);

        parent::__construct($context);

    }

    /*
     * Allow the request object to be created using the @canonical DocBlock comment
     */
    public static function createFromRequest(Request $request)
    {

        $self = new static($request->context);

        if (isset($request->context->getCaller()->annotations['canonical'])) {
            $self->setRouteParams(Url::extract($request->context->getCaller()->annotations['canonical'], $request->context->getUrl()->requestUri));
        } else {
            throw new Exception\MissingDocBlockException('RouteParams Request class requires @canonical DocBlock on calling method.');
        }

        return $self;

    }

    /*
     * Set the local route parameter variable
     */
    public function setRouteParams($routeParams)
    {

        $this->routeParams = $routeParams;

    }

    /*
     * Return all properties as an array
     */
    public function toArray()
    {

        return $this->routeParams;

    }

    /*
     * Magic getter method maps requests to the protected $get property
     */
    public function __get($property)
    {

        return (isset($this->routeParams[$property]) ? $this->routeParams[$property] : null);

    }

}
