<?php

namespace Bono\Middleware;

use Bono\Middleware;
use ROH\Util\Options;
use ROH\Util\Injector;
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

        $options = (new Options([
            'templatePaths' => [ '../templates' ],
            'accepts' => [
                'text/html' => true,
            ],
        ]))->merge($options);

        if (!isset($options['renderer'])) {
            throw new BonoException('Renderer must be set!');
        }

        parent::__construct($options);

    }

    public function __invoke(Context $context, callable $next)
    {
        $context['@renderer'] = $context['@renderer'] ?: $this;

        try {
            $next($context);
        } catch (ContextException $err) {
            $context->setStatus($err->getStatusCode());
        }

        if (
            (!($context['@renderer.rendered'] || $context->getResponse()->hasBody())) &&
            $this['accepts'][$context->getContentType() ?: 'text/html']
        ) {
            $statusCode = $context->getStatusCode();
            if ($statusCode >= 500) {
                // $context['@renderer.template'] = $statusCode;
            } elseif ($statusCode < 300 || $statusCode >= 400) {
                switch ($statusCode) {
                    case 401:
                    case 403:
                    case 404:
                    case 405:
                        $context['@renderer.template'] = $statusCode;
                        break;
                    default:
                        if (isset($context['@renderer.template'])) {
                            break;
                        }

                        if (isset($context['route.info'][1]['template'])) {
                            $context['@renderer.template'] = $context['route.info'][1]['template'];
                            break;
                        }

                        $lastSeparator = strrpos($context['route.uri']->getBasePath(), '/');
                        $bundle = substr($context['route.uri']->getBasePath(), $lastSeparator + 1);
                        $action = trim($context['route.info'][1]['uri'], '/') ?: 'index';

                        $context['@renderer.template'] = $bundle . ($bundle ? '/' . $action : $action);
                        break;
                }

                $this->write($context);
                $context['@renderer.rendered'] = true;
            }
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
        if (null === $this->renderer) {
            $this->renderer = $this->app->getInjector()->resolve($this['renderer'], [
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
