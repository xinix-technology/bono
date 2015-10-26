<?php

namespace Bono\Middleware;

use Bono\Middleware;
use ROH\Util\Options;
use ROH\Util\Thing;
use ROH\Util\Collection as UtilCollection;
use Bono\Http\Context;
use Bono\Exception\ContextException;

class TemplateRenderer extends UtilCollection
{
    protected $renderer;

    public function __construct($options = [])
    {
        $options = Options::create([
            'templatePath' => '../templates',
            'accepts' => [
                'text/html' => true,
            ],
        ])->merge($options);

        parent::__construct($options);

        if (is_null($this['renderer'])) {
            throw new \Exception('Renderer not set yet!');
        }

        $this->renderer = new Thing($this['renderer']);

    }

    public function __invoke(Context $context, $next)
    {
        $context['renderer'] = $this;

        try {
            $next($context);
        } catch (ContextException $err) {
            $context->withStatus($err->getStatusCode());
            $context['error'] = $err;
        }

        if ($context['renderer.use'] && $this['accepts'][$context->getContentType()]) {
            $template = trim($context['template']
                ?: $context->getUri()->getPathname(), '/')
                ?: 'index';

            $handler = $this->renderer->getHandler();
            $handler($context, $template);
            $context['renderer.use'] = false;
        }

        return $context;
    }
}
