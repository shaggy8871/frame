<?php

namespace Frame\Tests;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

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
