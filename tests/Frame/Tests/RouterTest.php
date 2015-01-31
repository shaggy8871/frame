<?php

namespace Frame\Tests;

use Frame\Core\Project;
use Frame\Core\Url;
use Frame\Core\UrlFactory;
use Frame\Core\Router;

class RouterTest extends \PHPUnit_Framework_TestCase {

    private $router;

    /*
     * Route all requests into Frame/Tests/Controllers with debugMode enabled
     */
    public function __construct()
    {

        $this->router = new Router(new Project('Frame\\Tests', 'tests', true));

    }

    public function testUrlFactoryAutoDetect1()
    {

        // Instantiate test $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'www.testframe.com';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        $_SERVER['PATH_INFO'] = '/';
        $_SERVER['QUERY_STRING'] = 'a=b';

        $url = UrlFactory::autodetect();

        // Make sure we get it back as expected
        $this->assertEquals($url->requestMethod, 'GET');
        $this->assertEquals($url->requestUri, '/');
        $this->assertEquals($url->rootUri, '');
        $this->assertEquals($url->rootBasePath, '');
        $this->assertEquals($url->scheme, 'http');
        $this->assertEquals($url->host, 'www.testframe.com');
        $this->assertEquals($url->port, 80);
        $this->assertEquals($url->pathComponents, ['']);
        $this->assertEquals($url->queryString, 'a=b');

    }

    public function testUrlFactoryAutoDetect2()
    {

        // Instantiate test $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'www.testframe.com';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SCRIPT_NAME'] = '/frame/public/index.php';
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        $_SERVER['PATH_INFO'] = '/';
        $_SERVER['QUERY_STRING'] = 'a=b';

        $url = UrlFactory::autodetect();

        // Test with getter methods
        $this->assertEquals($url->getRequestMethod(), 'GET');
        $this->assertEquals($url->getRequestUri(), '/');
        $this->assertEquals($url->getRootUri(), '/frame/public/index.php');
        $this->assertEquals($url->getRootBasePath(), '/frame/public');
        $this->assertEquals($url->getScheme(), 'http');
        $this->assertEquals($url->getHost(), 'www.testframe.com');
        $this->assertEquals($url->getPort(), 80);
        $this->assertEquals($url->getPathComponents(), ['']);
        $this->assertEquals($url->getQueryString(), 'a=b');

    }

    public function testIndexRouteDefault()
    {

        $this->expectOutputString('RouteDefault');

        $this->router->parseUrl($this->generateUrl('/'));

    }

    public function testIndexRouteJsonResponse()
    {

        $this->expectOutputString(json_encode(['json' => true]));

        $this->router->parseUrl($this->generateUrl('/jsonResponse'));

    }

    public function testIndexRouteJsonpResponse()
    {

        $this->expectOutputString(sprintf('%s(%s)', 'jsonp', json_encode(['jsonp' => true])));

        $this->router->parseUrl($this->generateUrl('/jsonpResponse'));

    }

    public function testIndexRouteTwigResponse()
    {

        $this->expectOutputRegex("/RouteTwigResponseOkay/");

        $this->router->parseUrl($this->generateUrl('/twigResponse'));

    }

    public function testIndexRouteUrlParamsRequest()
    {

        $this->expectOutputString(json_encode(['id' => '123', 'slug' => 'sluggish']));

        $this->router->parseUrl($this->generateUrl('/index/urlParamsRequest/123/sluggish'));

    }


    public function testProductsRouteDefault()
    {

        $this->expectOutputString('ProductsRouteDefault');

        $this->router->parseUrl($this->generateUrl('/products'));

    }

    public function testProductsRouteDefaultWithTrailingSlash()
    {

        $this->expectOutputString('ProductsRouteDefault');

        $this->router->parseUrl($this->generateUrl('/products/'));

    }

    public function testProductsRouteSubDir()
    {

        $this->expectOutputString('ProductsRouteSubDir');

        $this->router->parseUrl($this->generateUrl('/products/subdir'));

    }

    public function testProductsRouteSubDirWithTrailingSlash()
    {

        $this->expectOutputString('ProductsRouteSubDir');

        $this->router->parseUrl($this->generateUrl('/products/subdir/'));

    }

    public function testUrlFor()
    {

        $this->expectOutputString('/urlDestination/val');
        $this->router->parseUrl($this->generateUrl('/urlFor'));

    }

    public function testUrlForHome()
    {

        $this->expectOutputString('/');
        $this->router->parseUrl($this->generateUrl('/urlForHome'));

    }

    public function testUrlForAutodetect1()
    {

        $this->expectOutputString('/urldestinationautodetect');
        $this->router->parseUrl($this->generateUrl('/urlforautodetect1'));

    }

    public function testUrlForAutodetect2()
    {

        $this->expectOutputString('/products/urldestinationautodetect');
        $this->router->parseUrl($this->generateUrl('/urlforautodetect2'));

    }

    public function testUrlForFallback1()
    {

        $this->expectOutputString('/urlParamsRequest/123/slugger');
        $this->router->parseUrl($this->generateUrl('/urlForFallback1'));

    }

    public function testUrlForFallback2()
    {

        $this->expectOutputString('/products/urlDestination/val');
        $this->router->parseUrl($this->generateUrl('/urlForFallback2'));

    }

    public function testUrlForWithTwig()
    {

        $this->expectOutputRegex("/RouteTwigResponse: \/urlDestination\/val: \/products\/urlDestination\/val/");

        $this->router->parseUrl($this->generateUrl('/twigUrlFor'));

    }

    public function testFlash()
    {

        $this->expectOutputString(json_encode(['with' => 'flash']));

        $this->router->parseUrl($this->generateUrl('/flash'));

    }

    /*
     * Construct a Url object using the supplied requestUri
     */
    private function generateUrl($requestUri)
    {

        $pathComponents = explode('/', substr($requestUri, 1));

        return new Url([
            'pathComponents' => $pathComponents,
            'requestUri' => $requestUri
        ]);

    }

}
