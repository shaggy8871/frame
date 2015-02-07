<?php

namespace Frame\Tests\Models;

use Frame\Request\Request;

class InstantiateRequest extends Request
{

    public static function createFromRequest(Request $request)
    {

        echo "TestsModelsInstantiateRequest";

        return new static($request->getContext());

    }

}
