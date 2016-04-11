<?php

namespace Bono\Test;

use Bono\Test\BonoTestCase;
use Bono\Bundle;
use Bono\Http\Context;

class MiddlewareTest extends BonoTestCase
{
    public function testUseMiddlewareAsFunction()
    {
        $bundle = $this->app->resolve(Bundle::class);

        $hit = 0;
        $bundle->addMiddleware(function($context, $next) use (&$hit) {
            $hit++;
            $next($context);
        });

        $context = $this->app->resolve(Context::class);
        $bundle->dispatch($context);
        $this->assertEquals($hit, 1);
    }
}