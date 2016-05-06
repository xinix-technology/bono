<?php

namespace Bono\Test;

use Bono\Test\BonoTestCase;
use Bono\Bundle;
use Bono\Http\Context;
use ROH\Util\Injector;

class MiddlewareTest extends BonoTestCase
{
    public function testUseMiddlewareAsFunction()
    {
        $bundle = Injector::getInstance()->resolve(Bundle::class);

        $hit = 0;
        $bundle->addMiddleware(function($context, $next) use (&$hit) {
            $hit++;
            $next($context);
        });

        $context = Injector::getInstance()->resolve(Context::class);
        $bundle->dispatch($context);
        $this->assertEquals($hit, 1);
    }
}