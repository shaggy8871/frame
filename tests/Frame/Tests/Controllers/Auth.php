<?php

namespace Frame\Tests\Controllers;

use Frame\Core\Controller;
use Frame\Core\Url;
use Frame\Core\Context;
use Frame\Request\RouteParams;

class Auth extends Controller
{

    /**
     * @before authUser
     */
    public function routeAllowed()
    {

        return "routeAuthOkay";

    }

    /**
     * @before authUser
     */
    public function routeNotAllowed()
    {

        return "routeAuthShouldNotSeeThis";

    }

    /**
     * @before authUser
     */
    public function routeNotAllowedOutside()
    {

        return "routeAuthShouldNotSeeThis";

    }

    public function routeNotAuthorized()
    {

        return "routeNotAuthorized";

    }

    /**
     * Authorizes the user
     */
    public function authUser(Url $url, Context $context)
    {

        // One method:
        switch($url->getRequestUri()) {
            case '/auth/notallowed':
                return 'routeNotAuthorized';
        }

        // Another method:
        switch($context->getCaller()->getMethod()) {
            case 'routeNotallowedoutside': // Note case change!
                return 'Index::routeNotAuthorized';
        }

        // No response allows the rest through

    }

}
