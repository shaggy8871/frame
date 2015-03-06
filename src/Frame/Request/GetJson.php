<?php

namespace Frame\Request;

class GetJson extends Get implements RequestInterface
{

    /*
     * GET values are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        parent::__construct($context);

        $this->get = json_decode($_SERVER['QUERY_STRING'], true);

    }

}
