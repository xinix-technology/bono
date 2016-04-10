<?php

namespace Bono\Middleware;

use Bono\Middleware;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Bono\Http\Context;
use Bono\Exception\BonoException;
use Bono\Renderer\RendererInterface;
use Bono\Exception\ContextException;
use Bono\Renderer;
use Bono\App;

class TemplateRenderer extends UtilCollection
{
    protected $renderer;

    protected $app;

    public function __construct(App $app, $options = [])
    {
        $this->app = $app;

        $options = Options::create([
            'templatePaths' => [ '../templates' ],
            'accepts' => [
                'text/html' => true,
            ],
        ])->merge($options);

        if (is_null($options['renderer'])) {
            throw new BonoException('Renderer must be set!');
        }

        parent::__construct($options);

    }

    public function __invoke(Context $context, $next)
    {
        $context['@renderer'] = $context['@renderer'] ?: $this;

        try {
            $next($context);
        } catch (ContextException $err) {
            $context->setStatus($err->getStatusCode());
        }

        if (!$context['response.rendered'] && $this['accepts'][$context->getContentType() ?: 'text/html']) {
            switch ($context->getStatusCode()) {
                case 200:
                    if (isset($context['response.template'])) {
                        break;
                    }

                    if (isset($context['route.info'][1]['template'])) {
                        $context['response.template'] = $context['route.info'][1]['template'];
                        break;
                    }

                    $lastSeparator = strrpos($context['route.uri']->getBasePath(), '/');
                    $bundle = substr($context['route.uri']->getBasePath(), $lastSeparator + 1);
                    $action = trim($context['route.info'][1]['uri'], '/') ?: 'index';

                    $context['response.template'] = $bundle . ($bundle ? '/' . $action : $action);
                    break;
                case 404:
                    $context['response.template'] = 'notFound';
                    break;
                case 405:
                    $context['response.template'] = 'methodNotAllowed';
                    break;
            }

            $this->write($context);
            $context['response.rendered'] = true;
        }
    }

    public function write(Context $context)
    {
        return $this->getRenderer()->write($context);
    }

    public function resolve($template)
    {
        return $this->getRenderer()->resolve($template);
    }

    public function render($template, array $data = [])
    {
        return $this->getRenderer()->render($template, $data);
    }

    public function addTemplatePath($templatePath)
    {
        $templatePaths = $this['templatePaths'];
        $templatePaths[] = $templatePath;
        $this['templatePaths'] = $templatePaths;
        return $this;
    }

    protected function getRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = $this->app->resolve($this['renderer'], [
                'options' => [
                    'middleware' => $this,
                ]
            ]);

            if (!($this->renderer instanceof RendererInterface)) {
                throw new BonoException('Renderer must be instance of RendererInterface');
            }
        }

        return $this->renderer;
    }
}
