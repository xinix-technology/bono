<?php
namespace Bono\Middleware;

use Bono\Http\Context;

class StaticPage
{
    public function __invoke(Context $context, $next)
    {
        $renderer = $context['@renderer'];
        if (is_null($renderer)) {
            $next($context);
            return;
        }

        $templatePath = $renderer['templatePath'];

        $template = trim($context->getUri()->getPath(), '/') ?: 'index';

        if ($renderer->resolve($template)) {
            $context->setStatus(200)->setContentType('text/html');
            $context['response.template'] = $template;
        } else {
            $next($context);
        }
    }
}
