<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Middleware\MethodOverride;

class MethodOverrideTest extends BonoTestCase
{
    public function testInvokeAsCli()
    {
        $middleware = $this->app->resolve(MethodOverride::class);
        $context = $this->app->resolve(Context::class);

        $context->setRequest($context->getRequest()->withUri($context->getRequest()->getUri()->withQuery('?!method=put')));
        $middleware($context, function() {});
        $this->assertEquals($context->getMethod(), 'GET');
    }

    public function testInvokeAsNonCli()
    {
        $this->app['cli'] = false;
        $middleware = $this->app->resolve(MethodOverride::class);
        $context = $this->app->resolve(Context::class);

        $context->setRequest($context->getRequest()->withUri($context->getRequest()->getUri()->withQuery('?!method=put')));
        $middleware($context, function() {});
        $this->assertEquals($context->getMethod(), 'PUT');
    }
}