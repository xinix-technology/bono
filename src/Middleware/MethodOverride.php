<?php
namespace Bono\Middleware;

use Bono\Executor;
use Bono\Http\Context;

class MethodOverride
{
    /**
     * @var Executor
     */
    protected $executor;

    public function __construct(Executor $executor)
    {
        $this->executor = $executor;
    }

    public function __invoke(Context $context, callable $next)
    {
        if (!$this->executor['process.cli']) {
            $method = $context->getParam('!method');
            $context->removeParam('!method');
            if (null !== $method) {
                $context->setMethod($method);
            }
        }

        $next($context);
    }
}
