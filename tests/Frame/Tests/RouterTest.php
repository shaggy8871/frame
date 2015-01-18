<?php

namespace Frame\Tests;

use Frame\Core\Project;
use Frame\Core\Router;

class RouterTest extends \PHPUnit_Framework_TestCase {

    private $router;

    /*
     * Route all requests into Frame/Tests/Controllers with debugMode enabled
     */
    public function __construct()
    {

        $this->router = new Router(new Project('Frame\\Tests', 'Frame/Tests', true));

    }

    public function testWebHomePage()
    {

        $this->expectOutputString('WebHomePage');

        $this->router->parseUrl($this->generateUrl('/'));

    }

    public function testWebFullHomePage()
    {

        $this->expectOutputString('WebHomePage');

        $this->router->parseUrl($this->generateUrl('/index.php/'));

    }

    /*
     * Construct a UrlSim object using a dummy host and the supplied requestUri
     */
    private function generateUrl($requestUri)
    {

        $pathComponents = parse_url('http://www.testframe.com' . $requestUri);

        $url = new UrlSim();
        $url->pathComponents = explode('/', substr($pathComponents['path'], 1));

        return $url;

    }

}
