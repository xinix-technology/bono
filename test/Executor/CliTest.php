<?php

namespace Bono\Test\Executor;

use PHPUnit\Framework\TestCase;
use ROH\Util\Injector;
use Bono\Executor\Cli;
use Bono\Bundle;

class CliTest extends TestCase
{
    public function testConcreteBundle()
    {
        $injector = new Injector();

        $bundle = new Bundle($injector, [
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
                ],[
                    'uri' => '/baz',
                    'handler' => function ($ctx) {
                        throw new \Exception('Ouch');
                    }
                ],
            ],
        ]);

        $executor = new Cli($injector, $bundle);

        $ctx = $executor->run(['exe']);
        $this->assertEquals($ctx->getStatusCode(), 200);
        $this->assertEquals($ctx->getBody() . '', 'foo');

        $ctx = $executor->run(['exe', 'foo']);
        $this->assertEquals($ctx->getStatusCode(), 201);
        $this->assertEquals($ctx->getBody() . '', 'invoke foo');

        $ctx = $executor->run(['exe', 'baz']);
        $this->assertEquals($ctx->getStatusCode(), 500);

        $ctx = $executor->run(['exe', 'bar']);
        $this->assertEquals($ctx->getStatusCode(), 404);
    }
}
