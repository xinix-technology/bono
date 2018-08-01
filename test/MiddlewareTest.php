<?php

namespace Bono\Test;

use PHPUnit\Framework\TestCase;
use Bono\Bundle;
use Bono\Http\Context;
use ROH\Util\Injector;

class MiddlewareTest extends TestCase
{
    public function testUseMiddlewareAsFunction()
    {
        $bundle = (new Injector())->resolve(Bundle::class);

        $hit = 0;
        $bundle->addMiddleware(function ($context, $next) use (&$hit) {
            $hit++;
            $next($context);
        });

        $context = (new Injector())->resolve(Context::class);
        $bundle->dispatch($context);
        $this->assertEquals($hit, 1);
    }
}
