<?php

namespace Bono\Test\Executor;

use PHPUnit\Framework\TestCase;
use ROH\Util\Injector;
use Bono\Executor\Web;
use Bono\Bundle;

class WebTest extends TestCase
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

        $executor = new Web($injector, $bundle);

        $serverVars = $this->createServerVarsFromUri('');
        $ctx = $executor->run($serverVars, [], true);
        $this->assertEquals($ctx->getStatusCode(), 200);
        $this->assertEquals($ctx->getBody() . '', 'foo');

        $serverVars = $this->createServerVarsFromUri('/foo');
        $ctx = $executor->run($serverVars, [], true);
        $this->assertEquals($ctx->getStatusCode(), 201);
        $this->assertEquals($ctx->getBody() . '', 'invoke foo');

        $serverVars = $this->createServerVarsFromUri('/baz');
        $ctx = $executor->run($serverVars, [], true);
        $this->assertEquals($ctx->getStatusCode(), 500);

        $serverVars = $this->createServerVarsFromUri('/bar');
        $ctx = $executor->run($serverVars, [], true);
        $this->assertEquals($ctx->getStatusCode(), 404);
    }

    protected function createServerVarsFromUri(string $uri)
    {
        $serverVars = [];
        foreach ($_SERVER as $key => $value) {
            $serverVars[$key] = $value;
        }

        $serverVars['REQUEST_URI'] = $uri;

        return $serverVars;
    }
}
