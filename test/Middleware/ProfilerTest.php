<?php

namespace Bono\Test\Middleware;

use PHPUnit_Framework_TestCase;
use Bono\Middleware\Profiler;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $m = new Profiler();
        $context = $this->getMock(Context::class, [], [
            $this->getMock(Request::class),
            $this->getMock(Response::class),
        ]);

        $set = [];

        $context->expects($this->any())
            ->method('withHeader')
            ->will($this->returnCallback(function ($key) use ($context, &$set) {
                $set[] = $key;
                return $context;
            }));
        $next = function () {
        };

        $m($context, $next);

        $this->assertContains('X-Profiler-Response-Time', $set);
        $this->assertContains('X-Profiler-Memory-Usage', $set);
        $this->assertContains('X-Profiler-Peak-Memory-Usage', $set);
    }
}
