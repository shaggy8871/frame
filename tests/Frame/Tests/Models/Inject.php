<?php

namespace Frame\Tests\Models;

class Inject
{

    public function __construct(\Frame\Core\Context $context)
    {

        echo "TestsModelsInject";

    }

}
