<?php

namespace Bono\Middleware;

use Bono\Middleware;
use ROH\Util\Options;
use ROH\Util\Thing;

class TemplateRenderer extends Middleware
{
    protected $renderer;

    public function __construct($options = [])
    {
        $options = Options::create([
            'templatePath' => '../templates',
            'accepts' => [
                'text/html' => null,
            ],
        ])->merge($options);

        parent::__construct($options);

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
        $request['$templateRenderer'] = $this;

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
