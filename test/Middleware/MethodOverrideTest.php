<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Middleware\MethodOverride;
use ROH\Util\Injector;

class MethodOverrideTest extends BonoTestCase
{
    public function testInvokeAsCli()
    {
        $middleware = Injector::getInstance()->resolve(MethodOverride::class);
        $context = Injector::getInstance()->resolve(Context::class);

        $context->setRequest($context->getRequest()->withUri($context->getRequest()->getUri()->withQuery('?!method=put')));
        $middleware($context, function() {});
        $this->assertEquals($context->getMethod(), 'GET');
    }

    public function testInvokeAsNonCli()
    {
        $this->app['cli'] = false;
        $middleware = Injector::getInstance()->resolve(MethodOverride::class);
        $context = Injector::getInstance()->resolve(Context::class);

        $context->setRequest($context->getRequest()->withUri($context->getRequest()->getUri()->withQuery('?!method=put')));
        $middleware($context, function() {});
        $this->assertEquals($context->getMethod(), 'PUT');
    }
}