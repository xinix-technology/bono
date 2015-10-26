<?php
namespace Bono\Middleware;

use Bono\Http\Context;

class MethodOverride
{
    public function __invoke(Context $context, $next)
    {
        $method = $context->getParam('!method');
        if (isset($method)) {
            $context->withMethod($method);
        }

        $next($context);
    }
}
