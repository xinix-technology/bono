<?php
namespace Bono\Middleware;

class Profiler
{
    public function __invoke($request, $next)
    {
        $start = microtime(true);
        $response = $next($request);
        if (is_null($response)) {
            $response = new Response();
        }
        $time = (microtime(true) - $start) * 1000;
        return $response
            ->withHeader('X-Profiler-Response-Time', sprintf('%0.4f ms', $time))
            ->withHeader('X-Profiler-Memory-Usage', sprintf('%0.2fkB', memory_get_usage() / 1024))
            ->withHeader('X-Profiler-Peak-Memory-Usage', sprintf('%0.2fkB', memory_get_peak_usage() / 1024));
    }
}
