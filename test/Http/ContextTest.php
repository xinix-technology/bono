<?php
namespace Bono\Test\Http;

use PHPUnit_Framework_TestCase;
use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Exception\BonoException;

class ContextTest extends BonoTestCase {
    public function testGetAttributes()
    {
        $context = $this->app->resolve(Context::class);

        $context['foo'] = 'bar';

        $attributes = $context->getAttributes();
        $reqAttributes = $context->getRequest()->getAttributes();

        foreach ($attributes as $key => $value) {
            $this->assertEquals($attributes[$key], $reqAttributes[$key]);
        }
    }

    public function testGetParam()
    {
        $context = $this->getMock(Context::class, ['getParsedBody'], [
            $this->app,
            new Request(),
            new Response()
        ]);

        $context->method('getParsedBody')->will($this->returnValue([
            'post-foo' => 'post-bar',
        ]));

        $result = $context->getParam('foo', 'baz');
        $this->assertEquals($result, 'baz');

        $result = $context->getParam('post-foo');
        $this->assertEquals($result, 'post-bar');
    }
}