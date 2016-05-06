<?php
namespace Bono\Test;

use Bono\Bundle;
use Bono\App;
use Bono\Http\Context;
use Bono\Http\Uri;
use Bono\Http\Request;
use Bono\Http\Response;
use ROH\Util\Injector;

class BundleTest extends BonoTestCase
{
    public function testConstructDoMergeOptions()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class, [
            'options' => [
                'foo' => 'bar',
            ]
        ]);

        $this->assertEquals('bar', $bundle['foo']);
    }

    public function testGetReturnOption()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class, [
            'options' => [
                'foo' => 'bar',
            ]
        ]);

        $this->assertEquals('bar', $bundle->get('foo'));
        $this->assertEquals('doh', $bundle->get('missing', 'doh'));
    }

    public function testAddBundle()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class);

        $result = $bundle->addBundle([
            'uri' =>  '/',
            'handler' => [ Bundle::class ]
        ]);

        $this->assertEquals($bundle, $result);

        try {
            $result = $bundle->addBundle([
                'handler' => [ Bundle::class ]
            ]);
            $this->fail('Except throw error if missing uri');
        } catch(\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }

        try {
            $result = $bundle->addBundle([
                'uri' =>  '/',
            ]);
            $this->fail('Except throw error if missing handler');
        } catch(\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }
    }

    public function testRoutes()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class);

        $getRoute = function () {

        };
        $bundle->routeGet(['uri' => '/get', 'handler' => $getRoute]);

        $postRoute = function () {

        };
        $bundle->routePost(['uri' => '/post', 'handler' => $postRoute]);

        $putRoute = function () {

        };
        $bundle->routePut(['uri' => '/put', 'handler' => $putRoute]);

        $deleteRoute = function () {

        };
        $bundle->routeDelete(['uri' => '/delete', 'handler' => $deleteRoute]);

        $anyRoute = function () {

        };
        $bundle->routeAny(['uri' => '/any', 'handler' => $anyRoute]);

        $route = function() {};
        try {
            $bundle->routeMap(['handler' => $route]);
            $this->fail('Uri not specified');
        } catch(\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }


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

        $this->assertEquals(['DELETE'], $dumped[3]['methods']);
        $this->assertEquals('/delete', $dumped[3]['pattern']);
        $this->assertEquals($deleteRoute, $dumped[3]['handler']);

        $this->assertTrue(in_array('GET', $dumped[4]['methods']));
        $this->assertTrue(in_array('POST', $dumped[4]['methods']));
        $this->assertTrue(in_array('PUT', $dumped[4]['methods']));
        $this->assertTrue(in_array('DELETE', $dumped[4]['methods']));
        $this->assertEquals('/any', $dumped[4]['pattern']);
        $this->assertEquals($anyRoute, $dumped[4]['handler']);
    }

    public function testDispatchRunMiddleware()
    {
        $middlewareMock = $this->getMock(stdClass::class, ['first', 'second']);
        $middlewareMock->expects($this->once())
            ->method('first');
        $middlewareMock->expects($this->once())
            ->method('second');


        $bundle = $this->getMock(Bundle::class, ['__invoke'], [$this->app]);
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

        $context = Injector::getInstance()->resolve(Context::class);
        $bundle->dispatch($context);
    }

    public function testDispatchToSubBundle()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class);
        $fooBundle = $this->getMock(Bundle::class, ['dispatch'], [$this->app]);
        $fooBundle->expects($this->once())
            ->method('dispatch');

        $bundle->addBundle([
            'uri' => '/foo',
            'handler' => $fooBundle,
        ]);

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/foo')),
        ]);
        $bundle->dispatch($context);
    }

    public function testInvokeAsFunction()
    {
        $context = Injector::getInstance()->resolve(Context::class);

        $bundle = $this->getMock(Bundle::class, null, [$this->app]);
        try {
            $bundle($context);
            $this->fail('Should caught exception');
        } catch (\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }
    }

    public function testInvokeRoute()
    {
        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/foo/someBar')),
        ]);

        $hits = 0;

        $bundle = Injector::getInstance()->resolve(Bundle::class, [
            'options' => [
                'routes' => [
                    [
                        'uri' => '/foo/{bar}',
                        'handler' => function($context) use (&$hits) {
                            $hits++;
                            $this->assertEquals($context['bar'], 'someBar');
                            return [
                                'foo' => 'bar'
                            ];
                        }
                    ]
                ]
            ]
        ]);


        $bundle->dispatch($context);
        $this->assertEquals($hits, 1);
        $this->assertEquals($context->getState()['foo'], 'bar');
    }

    public function testInvokeMethodNotAllowed()
    {
        $context = $this->getMock(Context::class, [], [
            $this->app,
            $this->getMock(Request::class),
            $this->getMock(Response::class),
        ]);
        $context->method('offsetGet')
            ->will($this->returnCallback(function ($key) {
                if ($key === 'route.info') {
                    return [2];
                }
            }));
        $context->expects($this->once())
            ->method('throwError');

        $bundle = Injector::getInstance()->resolve(Bundle::class);
        $bundle($context);
    }

    public function testDebugInfoShowMiddlewaresBundlesRoutesAttributes()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class, [
            'options' => [
                'middlewares' => [
                    function() {},
                ],
                'bundles' => [
                    [
                        'uri' => '/foo',
                        'handler' => $this->getMock(Bundle::class, [], [$this->app]),
                    ],
                ],
                'routes' => [
                    [
                        'uri' => '/bar',
                        'handler' => function() {}
                    ]
                ],
                'baz' => 'custom attribute'
            ]
        ]);

        $debug = $bundle->__debugInfo();
        $this->assertTrue(is_array($debug['middlewares']));
        $this->assertTrue(is_array($debug['bundles']));
        $this->assertTrue(is_array($debug['routes']));
        $this->assertTrue(is_array($debug['attributes']));
    }
}
