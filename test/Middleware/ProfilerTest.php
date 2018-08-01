<?php

namespace Bono\Test\Middleware;

use Bono\Middleware\Profiler;
use PHPUnit\Framework\TestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;

class ProfilerTest extends TestCase
{
    public function testInvoke()
    {
        $m = new Profiler();

        $context = $this->getMockBuilder(Context::class)
            // ->setMethods([])
            ->setConstructorArgs([
                $this->createMock(Request::class),
                $this->createMock(Response::class),
            ])
            ->getMock();

        $set = [];

        $context->expects($this->any())
            ->method('setHeader')
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
