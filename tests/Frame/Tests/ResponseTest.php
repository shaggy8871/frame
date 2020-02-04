<?php

namespace Frame\Tests;

use Frame\Request\Request;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testFlashWithMultipleKeys()
    {
        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $response = new \Frame\Response\Response($ctx);

        $response->flash('first_key', 'first_value');
        $response->flash('second_key', 'second_value');

        $request = new Request($ctx);
        $this->assertEquals('first_value', $request->getFlash('first_key'));
        $this->assertEquals('second_value', $request->getFlash('second_key'));
    }

    public function testResponseInterface()
    {

        $this->expectOutputString('Hello world!');

        $project = new \Frame\Core\Project(__NAMESPACE__, 'tests', true);
        $ctx = new \Frame\Core\Context($project);
        $response = new \Frame\Response\Response($ctx);
        $response->setResponseClass(new \Frame\Response\Html($ctx))
                 ->setViewParams('Hello world!')
                 ->render();

    }

}
