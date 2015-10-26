<?php
namespace Bono\Middleware;

use Bono\Http\Context;

class Profiler
{
    public function __invoke(Context $context, $next)
    {
        $start = microtime(true);
        $next($context);
        $time = (microtime(true) - $start) * 1000;

        $context
            ->withHeader('X-Profiler-Response-Time', sprintf('%0.4f ms', $time))
            ->withHeader('X-Profiler-Memory-Usage', sprintf('%0.2fkB', memory_get_usage() / 1024))
            ->withHeader('X-Profiler-Peak-Memory-Usage', sprintf('%0.2fkB', memory_get_peak_usage() / 1024));
    }
}
