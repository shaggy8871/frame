<?php

namespace Frame\Tests\Controllers;

use Frame\Core\Controller;
use Frame\Core\Url;
use Frame\Request\RouteParams;

class CaSetEst extends Controller
{

    public function routeDefault()
    {

        return "CaseTestRouteDefault";

    }

    public function routeSubDir()
    {

        return "CaseTestRouteSubDir";

    }

    /**
     * @canonical /casetest/:id
     */
    public function routeNumbers(RouteParams $request)
    {

        return "CaseTestRouteNumbers" . $request->id;

    }

}
