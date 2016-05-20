<?php

namespace Bono\Middleware;

use Bono\Http\Stream;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Bono\Middleware;
use Bono\Http\Context;
use Bono\App;
use JsonKit\JsonKit;
use ROH\Util\Injector;
use Exception;

class ContentNegotiator extends UtilCollection
{
    protected $app;

    protected $options;

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;

        $options = (new Options([
                'renderers' => [
                    'application/json' => [$this, 'jsonRender'],
                ],
                'mapper' => [
                    'application/x-www-form-urlencoded' => 'text/html',
                    'multipart/form-data' => 'text/html',
                    'json' => 'application/json',
                ],
                'accepts' => ['text/html'],
            ]))
            ->merge($options);

        parent::__construct($options);
    }

    protected function negotiate(Context $context)
    {
        // if route already set content type, use this instead
        $responseContentType = $context->getContentType();
        if ($responseContentType) {
            return $responseContentType;
        }

        $extension = $context->getUri()->getExtension();
        if (null !== $extension && isset($this['mapper'][$extension])) {
            return $this['mapper'][$extension];
        }

        $contentType = $context->getRequest()->getContentType();
        if (null !== $contentType) {
            return isset($this['mapper'][$contentType]) ? $this['mapper'][$contentType] : $contentType;
        }

        return $context->accepts($this['accepts']);
    }

    public function __invoke(Context $context, callable $next)
    {
        // avoid content negotiator on cli
        if ($this->app->isCli()) {
            $next($context);
        } else {
            try {
                $next($context);
            } catch (Exception $e) {
                $lastError = $e;
            }
            $this->finalize($context);
            if (isset($lastError)) {
                throw $e;
            }
        }
    }

    protected function finalize(Context $context)
    {
        if ($context['@renderer.rendered']) {
            return;
        }

        $injector = $this->app->getInjector();

        $contentType = $this->negotiate($context);
        if ($contentType) {
            $context->setContentType($contentType);
            if (isset($this['renderers'][$contentType])) {
                $handler = $injector->resolve($this['renderers'][$contentType]);
                $handler($context);
                $context['@renderer.rendered'] = 'content-negotiator';
            // } elseif ($context->getBody()->getSize() === 0) {
            //     $context->throwError(
            //         406,
            //         'Content type "' . $contentType . '" not acceptable or unable to render properly'
            //     );
            }
        }
    }

    public function jsonRender(Context $context)
    {
        $body = new Stream();
        $statusCode = $context->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $context->setState('error', [
                'code' => $statusCode,
                'message' => $context->getResponse()->getReasonPhrase(),
            ]);
        }
        $body->write(JsonKit::encode($context->getState(), (array) $context->getAttributes()));
        $context = $context->setBody($body);
        return $context;
    }
}
