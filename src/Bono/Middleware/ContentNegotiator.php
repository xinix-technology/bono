<?php

namespace Bono\Middleware;

use Bono\Http\Stream;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Bono\Middleware;
use Bono\Http\Context;
use Bono\App;
use JsonKit\JsonKit;

class ContentNegotiator extends UtilCollection
{
    protected $app;

    protected $options;

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;

        $options = Options::create([
                'renderers' => [
                    'application/json' => [$this, 'jsonRender'],
                ],
                'mapper' => [
                    'application/x-www-form-urlencoded' => 'text/html',
                    'multipart/form-data' => 'text/html',
                    'json' => 'application/json',
                ],
                'accepts' => ['text/html'],
            ])
            ->merge($options);

        parent::__construct($options);
    }

    public function match($contentType)
    {
        $mapper = $this->options['mapper'];
        if (is_callable($mapper)) {
            return $mapper($contentType);
        } else {
            return isset($mapper[$contentType]) ? $mapper[$contentType] : $contentType;
        }
    }

    protected function negotiate(Context $context)
    {
        $extension = $context->getUri()->getExtension();
        if (isset($extension) && isset($this['mapper'][$extension])) {
            return $this['mapper'][$extension];
        }

        $contentType = $context->getRequest()->getContentType();
        if (isset($contentType)) {
            return isset($this['mapper'][$contentType]) ? $this['mapper'][$contentType] : $contentType;
        }

        return $context->accepts($this['accepts']);
    }

    public function __invoke(Context $context, $next = null)
    {
        // avoid content negotiator on cli
        if ($context->getApp()->isCli()) {
            $next($context);
            return;
        }

        $next($context);

        if ($context['response.rendered']) {
            return;
        }

        $contentType = $this->negotiate($context);
        if ($contentType) {
            $context->setContentType($contentType);
            if (isset($this['renderers'][$contentType])) {
                $handler = $this->app->resolve($this['renderers'][$contentType]);
                $handler($context);
                $context['response.rendered'] = 'content-negotiator';
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
        $body->write(JsonKit::encode($context->getState()));
        $context = $context->setBody($body);
        return $context;
    }
}
