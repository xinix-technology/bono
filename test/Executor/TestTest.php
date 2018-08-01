<?php

namespace Bono\Test\Executor;

use PHPUnit\Framework\TestCase;
use ROH\Util\Injector;
use Bono\Executor\Test;
use Bono\Bundle;

class TestTest extends TestCase
{
    public function testConcreteBundle()
    {
        $injector = new Injector();

        $bundle = new Bundle($injector);
        $bundle->routeGet([
            'uri' => '/',
            'handler' => function ($ctx) {
                $ctx->getBody()->write('foo');
            }
        ]);

        $bundle->routeGet([
            'uri' => '/foo',
            'handler' => function ($ctx) {
                $ctx->setStatus(201);
                $ctx->getBody()->write('invoke foo');
            }
        ]);

        $bundle->routeGet([
            'uri' => '/baz',
            'handler' => function ($ctx) {
                throw new \Exception('Ouch');
            }
        ]);

        $test = new Test($injector, $bundle);

        $ctx = $test->get('/')->run();
        $this->assertEquals($ctx->getStatusCode(), 200);
        $this->assertEquals($ctx->getBody() . '', 'foo');

        $ctx = $test->get('/foo')->run();
        $this->assertEquals($ctx->getStatusCode(), 201);
        $this->assertEquals($ctx->getBody() . '', 'invoke foo');

        $ctx = $test->get('/bar')->run();
        $this->assertEquals($ctx->getStatusCode(), 404);

        $ctx = $test->get('/baz')->run();
        $this->assertEquals($ctx->getStatusCode(), 500);
    }

    public function testBundleByDefinition()
    {
        $injector = new Injector();

        $bundle = new Bundle($injector);

        $test = new Test($injector, [ Bundle::class, [
            'options' => [
                'routes' => [
                    [
                        'uri' => '/',
                        'handler' => function ($ctx) {
                            $ctx->getBody()->write('foo');
                        }
                    ],
                    [
                        'uri' => '/foo',
                        'handler' => function ($ctx) {
                            $ctx->setStatus(201);
                            $ctx->getBody()->write('invoke foo');
                        }
                    ],
                    [
                        'uri' => '/baz',
                        'handler' => function ($ctx) {
                            throw new \Exception('Ouch');
                        }
                    ],
                ],
            ],
        ]]);

        $ctx = $test->get('/')->run();
        $this->assertEquals($ctx->getStatusCode(), 200);
        $this->assertEquals($ctx->getBody() . '', 'foo');

        $ctx = $test->get('/foo')->run();
        $this->assertEquals($ctx->getStatusCode(), 201);
        $this->assertEquals($ctx->getBody() . '', 'invoke foo');

        $ctx = $test->get('/bar')->run();
        $this->assertEquals($ctx->getStatusCode(), 404);

        $ctx = $test->get('/baz')->run();
        $this->assertEquals($ctx->getStatusCode(), 500);
    }
}
