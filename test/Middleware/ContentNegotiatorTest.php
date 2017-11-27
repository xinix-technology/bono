<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Middleware\ContentNegotiator;
use ROH\Util\Injector;

class ContentNegotiatorTest extends BonoTestCase
{
    public function testInvokeIfCli()
    {
        $middleware = Injector::getInstance()->resolve(ContentNegotiator::class);
        $context = Injector::getInstance()->resolve(Context::class);

        $middleware($context, function () {
        });
        $this->assertEquals($context['@renderer.rendered'], null);
    }

    public function testInvokeIfAlreadyRendered()
    {
        $this->app['cli'] = false;
        $middleware = Injector::getInstance()->resolve(ContentNegotiator::class);
        $context = Injector::getInstance()->resolve(Context::class);
        $context['@renderer.rendered'] = 'some-renderer';

        $hits = 0;
        $middleware($context, function () use (&$hits) {
            $hits++;
        });
        $this->assertEquals($hits, 1);
        $this->assertEquals($context['@renderer.rendered'], 'some-renderer');
    }

    public function testInvokeJson()
    {
        $this->app['cli'] = false;
        $middleware = Injector::getInstance()->resolve(ContentNegotiator::class);

        $context = Injector::getInstance()->resolve(Context::class);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/json'));

        $middleware($context, function ($context) {
            $context->setStatus(200)->setState(['foo' => 'bar']);
        });
        $this->assertEquals($context['@renderer.rendered'], 'content-negotiator');
        $this->assertEquals($context->getBody()->__toString(), json_encode(['foo' => 'bar']));

        $context = Injector::getInstance()->resolve(Context::class);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/json'));

        $middleware($context, function ($context) {
            $context->setStatus(412);
        });
        $this->assertEquals($context['@renderer.rendered'], 'content-negotiator');
        $this->assertTrue(strpos($context->getBody()->__toString(), '"code":412') >= 0);
    }

    public function testInvokeNegotiateJsonExtension()
    {
        $this->app['cli'] = false;
        $middleware = Injector::getInstance()->resolve(ContentNegotiator::class);

        $context = Injector::getInstance()->resolve(Context::class);
        $context->setRequest($context->getRequest()->withUri($context->getUri()->withPath('/foo.json')));

        $middleware($context, function ($context) {
            $context->setStatus(200)->setState(['foo' => 'bar']);
        });
        $this->assertEquals($context['@renderer.rendered'], 'content-negotiator');
        $this->assertEquals($context->getBody()->__toString(), json_encode(['foo' => 'bar']));
    }

    public function testInvokeNegotiateJsonAccept()
    {
        $this->app['cli'] = false;
        $middleware = Injector::getInstance()->resolve(ContentNegotiator::class, [
            'options' => [
                'accepts' => ['application/json']
            ]
        ]);

        $context = Injector::getInstance()->resolve(Context::class);
        $context->setRequest($context->getRequest()->withHeader('Accept', 'application/json'));

        $middleware($context, function ($context) {
            $context->setStatus(200)->setState(['foo' => 'bar']);
        });
        $this->assertEquals($context['@renderer.rendered'], 'content-negotiator');
        $this->assertEquals($context->getBody()->__toString(), json_encode(['foo' => 'bar']));
    }

    public function testInvokeThrowError()
    {
        $this->app['cli'] = false;
        $middleware = $this->getMockBuilder(ContentNegotiator::class)
            ->setMethods(['finalize'])
            ->setConstructorArgs([$this->app])
            ->getMock();
        $middleware->expects($this->once())->method('finalize');

        $context = Injector::getInstance()->resolve(Context::class);
        try {
            $middleware($context, function ($context) {
                $this->fail('Oops');
            });
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Oops') {
                throw $e;
            }
        }
    }
}
