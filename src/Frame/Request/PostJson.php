<?php

namespace Frame\Request;

class PostJson extends Post implements RequestInterface
{

    /*
     * POST values are simply stored as object properties - unsanitized!
     */
    public function __construct(\Frame\Core\Context $context)
    {

        parent::__construct($context);

        $this->post = json_decode(file_get_contents('php://input'), true);

    }

}
