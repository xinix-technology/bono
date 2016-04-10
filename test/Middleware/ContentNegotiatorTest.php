<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Middleware\ContentNegotiator;

class ContentNegotiatorTest extends BonoTestCase
{
    public function testInvokeIfCli()
    {
        $middleware = $this->app->resolve(ContentNegotiator::class);
        $context = $this->app->resolve(Context::class);

        $middleware($context, function() {});
        $this->assertEquals($context['response.rendered'], null);
    }

    public function testInvokeIfAlreadyRendered()
    {
        $this->app['cli'] = false;
        $middleware = $this->app->resolve(ContentNegotiator::class);
        $context = $this->app->resolve(Context::class);
        $context['response.rendered'] = 'some-renderer';

        $hits = 0;
        $middleware($context, function() use (&$hits) {
            $hits++;
        });
        $this->assertEquals($hits, 1);
        $this->assertEquals($context['response.rendered'], 'some-renderer');
    }

    public function testInvokeJson()
    {
        $this->app['cli'] = false;
        $middleware = $this->app->resolve(ContentNegotiator::class);

        $context = $this->app->resolve(Context::class);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/json'));

        $middleware($context, function($context) {
            $context->setStatus(200)->setState(['foo' => 'bar']);
        });
        $this->assertEquals($context['response.rendered'], 'content-negotiator');
        $this->assertEquals($context->getBody()->__toString(), json_encode(['foo' => 'bar']));

        $context = $this->app->resolve(Context::class);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/json'));

        $middleware($context, function($context) {
            $context->setStatus(412);
        });
        $this->assertEquals($context['response.rendered'], 'content-negotiator');
        $this->assertTrue(strpos($context->getBody()->__toString(), '"code":412') >= 0);
    }

    public function testInvokeNegotiateJsonExtension()
    {
        $this->app['cli'] = false;
        $middleware = $this->app->resolve(ContentNegotiator::class);

        $context = $this->app->resolve(Context::class);
        $context->setRequest($context->getRequest()->withUri($context->getUri()->withPath('/foo.json')));

        $middleware($context, function($context) {
            $context->setStatus(200)->setState(['foo' => 'bar']);
        });
        $this->assertEquals($context['response.rendered'], 'content-negotiator');
        $this->assertEquals($context->getBody()->__toString(), json_encode(['foo' => 'bar']));
    }

    public function testInvokeNegotiateJsonAccept()
    {
        $this->app['cli'] = false;
        $middleware = $this->app->resolve(ContentNegotiator::class, [
            'options' => [
                'accepts' => ['application/json']
            ]
        ]);

        $context = $this->app->resolve(Context::class);
        $context->setRequest($context->getRequest()->withHeader('Accept', 'application/json'));

        $middleware($context, function($context) {
            $context->setStatus(200)->setState(['foo' => 'bar']);
        });
        $this->assertEquals($context['response.rendered'], 'content-negotiator');
        $this->assertEquals($context->getBody()->__toString(), json_encode(['foo' => 'bar']));
    }
}