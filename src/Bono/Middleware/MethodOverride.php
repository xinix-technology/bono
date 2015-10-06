<?php
namespace Bono\Middleware;

class MethodOverride
{
    public function __invoke($request, $next)
    {
        $method = $request->getParam('!method');
        if (isset($method)) {
            $request = $request->withMethod($method);
        }
        return $next($request);
    }
}
