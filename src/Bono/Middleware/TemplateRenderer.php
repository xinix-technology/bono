<?php

namespace Bono\Middleware;

use ROH\Util\Options;
use ROH\Util\Thing;

class TemplateRenderer
{
    protected $options;

    protected $renderer;

    public function __construct($options = [])
    {
        $this->options = Options::create([
            'accepts' => [
                'text/html' => null,
            ],
        ])->merge($options);

        if (is_null($this->options['renderer'])) {
            throw new \Exception('Renderer not set yet!');
        }

        $this->renderer = new Thing($this->options['renderer']);
    }

    public function render($response, $template, $data = [])
    {
        $handler = $this->renderer->getHandler();
        $response = $handler($response, $template, $data);
        $response['isRendered'] = true;
        return $response;
    }

    public function __invoke($request, $next)
    {
        $response = $next($request);

        if (!$response['isRendered'] && array_key_exists($response->getContentType(), $this->options['accepts'])) {
            $template = trim($response['template']
                ?: $request->getUri()->getPathname(), '/')
                ?: 'index';

            $response = $this->render($response, $template, $response->getData());
        }
        return $response;
    }
}
