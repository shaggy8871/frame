<?php

namespace Frame\Tests;

use Frame\Core\Url;
use Frame\Core\Utils\Url as UrlUtils;
use Frame\Core\Caller;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function testGetIsset()
    {

        // Instantiate test variables
        parse_str('var1=1&var2=two', $_GET);

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $request = new \Frame\Request\Get($ctx);

        $this->assertEquals(isset($request->var1), true);
        $this->assertEquals($request->var1, 1);
        $this->assertEquals(isset($request->var2), true);
        $this->assertEquals($request->var2, 'two');
        $this->assertEquals(isset($request->var3), false);
        $this->assertEquals($request->var3, null);

    }

    public function testPostIsset()
    {

        // Instantiate test variables
        parse_str('var1=1&var2=two', $_POST);

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $request = new \Frame\Request\Post($ctx);

        $this->assertEquals(isset($request->var1), true);
        $this->assertEquals($request->var1, 1);
        $this->assertEquals(isset($request->var2), true);
        $this->assertEquals($request->var2, 'two');
        $this->assertEquals(isset($request->var3), false);
        $this->assertEquals($request->var3, null);

    }

    public function testPutIsset()
    {

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $request = new \Frame\Request\Put($ctx);
        $request->setProps([
            'var1' => 1,
            'var2' => 'two'
        ]);

        $this->assertEquals(isset($request->var1), true);
        $this->assertEquals($request->var1, 1);
        $this->assertEquals(isset($request->var2), true);
        $this->assertEquals($request->var2, 'two');
        $this->assertEquals(isset($request->var3), false);
        $this->assertEquals($request->var3, null);

    }

    public function testDeleteIsset()
    {

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $request = new \Frame\Request\Delete($ctx);
        $request->setProps([
            'var1' => 1,
            'var2' => 'two'
        ]);

        $this->assertEquals(isset($request->var1), true);
        $this->assertEquals($request->var1, 1);
        $this->assertEquals(isset($request->var2), true);
        $this->assertEquals($request->var2, 'two');
        $this->assertEquals(isset($request->var3), false);
        $this->assertEquals($request->var3, null);

    }

    public function testArgsIsset()
    {

        $GLOBALS['argv'] = [
            'var1' => 1,
            'var2' => 'two'
        ];

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $request = new \Frame\Request\Args($ctx);

        $this->assertEquals(isset($request->var1), true);
        $this->assertEquals($request->var1, 1);
        $this->assertEquals(isset($request->var2), true);
        $this->assertEquals($request->var2, 'two');
        $this->assertEquals(isset($request->var3), false);
        $this->assertEquals($request->var3, null);

    }

    public function testRouteParamsIsset()
    {

        $url = $this->generateUrl('/test/1/two');
        $caller = new Caller('Test', 'routeTest', [
            'canonical' => '/test/:var1/:var2'
        ]);

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project, $url, $caller);
        $request = new \Frame\Request\RouteParams($ctx);

        $this->assertEquals(isset($request->var1), true);
        $this->assertEquals($request->var1, 1);
        $this->assertEquals(isset($request->var2), true);
        $this->assertEquals($request->var2, 'two');
        $this->assertEquals(isset($request->var3), false);
        $this->assertEquals($request->var3, null);

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
