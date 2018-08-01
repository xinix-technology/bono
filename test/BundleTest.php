<?php
namespace Bono\Test;

use PHPUnit\Framework\TestCase;
use Bono\Bundle;
use Bono\Http\Context;
use Bono\Http\Uri;
use Bono\Http\Request;
use Bono\Http\Response;
use ROH\Util\Injector;

class BundleTest extends TestCase
{
    public function testConstructDoMergeOptions()
    {
        $bundle = (new Injector())->resolve(Bundle::class, [
            'options' => [
                'foo' => 'bar',
            ]
        ]);

        $this->assertEquals('bar', $bundle['foo']);
    }

    public function testGetReturnOption()
    {
        $bundle = (new Injector())->resolve(Bundle::class, [
            'options' => [
                'foo' => 'bar',
            ]
        ]);

        $this->assertEquals('bar', $bundle->get('foo'));
        $this->assertEquals('doh', $bundle->get('missing', 'doh'));
    }

    public function testAddBundle()
    {
        $bundle = (new Injector())->resolve(Bundle::class);

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
        } catch (\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }

        try {
            $result = $bundle->addBundle([
                'uri' =>  '/',
            ]);
            $this->fail('Except throw error if missing handler');
        } catch (\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }
    }

    public function testRoutes()
    {
        $bundle = (new Injector())->resolve(Bundle::class);

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

        $route = function () {
        };
        try {
            $bundle->routeMap(['handler' => $route]);
            $this->fail('Uri not specified');
        } catch (\Exception $e) {
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
        $middlewareMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['first', 'second'])
            ->getMock();

        $middlewareMock->expects($this->once())
            ->method('first');
        $middlewareMock->expects($this->once())
            ->method('second');


        $bundle = $this->getMockBuilder(Bundle::class)
            ->setMethods(['__invoke'])
            ->setConstructorArgs([new Injector()])
            ->getMock();

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

        $context = (new Injector())->resolve(Context::class);
        $bundle->dispatch($context);
    }

    public function testDispatchToSubBundle()
    {
        $bundle = (new Injector())->resolve(Bundle::class);
        $fooBundle = $this->getMockBuilder(Bundle::class)
            ->setMethods(['dispatch'])
            ->setConstructorArgs([new Injector()])
            ->getMock();

        $fooBundle->expects($this->once())
            ->method('dispatch');

        $bundle->addBundle([
            'uri' => '/foo',
            'handler' => $fooBundle,
        ]);

        $context = (new Injector())->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/foo')),
        ]);
        $bundle->dispatch($context);
    }

    public function testInvokeAsFunction()
    {
        $context = (new Injector())->resolve(Context::class);

        $bundle = $this->getMockBuilder(Bundle::class)
            ->setConstructorArgs([new Injector()])
            ->getMock();
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
        $context = (new Injector())->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/foo/someBar')),
        ]);

        $hits = 0;

        $bundle = (new Injector())->resolve(Bundle::class, [
            'options' => [
                'routes' => [
                    [
                        'uri' => '/foo/{bar}',
                        'handler' => function ($context) use (&$hits) {
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
        $context = $this->getMockBuilder(Context::class)
            ->setConstructorArgs([
                $this->createMock(Request::class),
                $this->createMock(Response::class),
            ])
            ->getMock();

        $context->method('offsetGet')
            ->will($this->returnCallback(function ($key) {
                if ($key === 'route.info') {
                    return [2];
                }
            }));
        $context->expects($this->once())
            ->method('throwError');

        $bundle = (new Injector())->resolve(Bundle::class);
        $bundle($context);
    }

    public function testDebugInfoShowMiddlewaresBundlesRoutesAttributes()
    {
        $foo = new \Exception();
        $bundle = (new Injector())->resolve(Bundle::class, [
            'options' => [
                'middlewares' => [
                    function () {
                    },
                    'trim',
                    [ 'Foo' ],
                    [ $foo, 'getMessage' ],
                ],
                'bundles' => [
                    [
                        'uri' => '/foo',
                        'handler' => $this->createMock(Bundle::class),
                    ],
                ],
                'routes' => [
                    [
                        'uri' => '/bar',
                        'handler' => function () {
                        }
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
