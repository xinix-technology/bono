<?php
namespace Bono\Test;

use Bono\Bundle;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use PHPUnit_Framework_TestCase;

class BundleTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWillMergeOptions()
    {
        $bundle = new Bundle([
            'foo' => 'bar',
        ]);

        $this->assertEquals('bar', $bundle['foo']);
    }

    public function testGetReturnOption()
    {
        $bundle = new Bundle([
            'foo' => 'bar',
        ]);

        $this->assertEquals('bar', $bundle->get('foo'));
        $this->assertEquals('doh', $bundle->get('missing', 'doh'));
    }

    public function testAddBundle()
    {
        $bundle = new Bundle();

        $result = $bundle->addBundle([
            'class' => Bundle::class,
        ]);

        $this->assertEquals($bundle, $result);
    }

    public function testRoutes()
    {
        $bundle = new Bundle();

        $getRoute = function () {

        };
        $bundle->routeGet('/get', $getRoute);

        $postRoute = function () {

        };
        $bundle->routePost('/post', $getRoute);

        $putRoute = function () {

        };
        $bundle->routePut('/put', $getRoute);


        $dumped = $bundle->dumpRoutes();

        $this->assertEquals(['GET'], $dumped[0]['methods']);
        $this->assertEquals('/get', $dumped[0]['pattern']);
        $this->assertEquals($getRoute, $dumped[0]['handler']);

        $this->assertEquals(['POST'], $dumped[1]['methods']);
        $this->assertEquals('/post', $dumped[1]['pattern']);
        $this->assertEquals($postRoute, $dumped[1]['handler']);

        $this->assertEquals(['PUT'], $dumped[2]['methods']);
        $this->assertEquals('/put', $dumped[2]['pattern']);
        $this->assertEquals($putRoute, $dumped[2]['handler']);
    }

    public function testDispatchRunMiddleware()
    {
        $middlewareMock = $this->getMock(stdClass::class, ['first', 'second']);
        $middlewareMock->expects($this->once())
            ->method('first');
        $middlewareMock->expects($this->once())
            ->method('second');

        $bundle = $this->getMock(Bundle::class, ['__invoke']);
        $bundle->method('__invoke')
            ->willReturn(null);

        $bundle->addMiddleware(function ($context, $next) use ($middlewareMock) {
            $middlewareMock->first();
            $next($context);
        });
        $bundle->addMiddleware(function ($context, $next) use ($middlewareMock) {
            $middlewareMock->second();
            $next($context);
        });
        $context = $this->getMock(Context::class, [], [
            $this->getMock(Request::class),
            $this->getMock(Response::class),
        ]);

        $bundle->dispatch($context);
    }

    public function testInvokeAsFunction()
    {
        $context = $this->getMock(Context::class, [], [
            $this->getMock(Request::class),
            $this->getMock(Response::class),
        ]);

        $bundle = $this->getMock(Bundle::class, null);
        try {
            $bundle($context);
            $this->fail('Should caught exception');
        } catch (\Exception $e) {
        }
    }

    public function testInvokeMethodNotAllowed()
    {
        $context = $this->getMock(Context::class, [], [
            $this->getMock(Request::class),
            $this->getMock(Response::class),
        ]);
        $context->method('offsetGet')
            ->will($this->returnCallback(function ($key) {
                if ($key === 'routeInfo') {
                    return [2];
                }
            }));
        $context->expects($this->once())
            ->method('throwError');

        $bundle = $this->getMock(Bundle::class, null);
        $bundle($context);
    }
}
