<?php
namespace Bono\Middleware;

use Bono\Http\Context;
use Bono\App;

class MethodOverride
{
    protected $app;

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;
    }

    public function __invoke(Context $context, $next)
    {
        if ($this->app->isCli()) {
            $next($context);
            return;
        }

        $method = $context->getParam('!method');
        if (isset($method)) {
            $context->setMethod($method);
        }

        $next($context);
    }
}
