<?php
namespace Bono\Middleware;

use Bono\Http\Context;

class StaticPage
{
    public function __invoke(Context $context, callable $next)
    {
        $renderer = $context['@renderer'];
        if (null === $renderer) {
            $next($context);
        } else {
            $template = '__static__/' . (trim($context->getUri()->getPath(), '/') ?: 'index');

            if ($renderer->resolve($template)) {
                $context->apply(function ($context) use ($template) {
                    $context->setStatus(200)->setContentType('text/html');
                    $context['@renderer.template'] = $template;
                });
            } else {
                $next($context);
            }
        }
    }
}
