<?php

namespace Bono\Middleware;

use Bono\Http\Stream;
use ROH\Util\Thing;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Bono\Middleware;
use Bono\Http\Context;

class ContentNegotiator extends UtilCollection
{
    protected $options;

    public function __construct(array $options = [])
    {

        $options = Options::create([
                'renderers' => [
                    // 'application/json' => [$this, 'renderJson'],
                ],
                'matchingTypes' => [
                    'application/x-www-form-urlencoded' => 'text/html',
                    'multipart/form-data' => 'text/html'
                ],
                'accepts' => [],
            ])
            ->merge($options);
            // ->toArray();

        parent::__construct($options);
    }

    // public function renderJson($response)
    // {
    //     $body = new Stream();
    //     $statusCode = $response->getStatusCode();
    //     if ($statusCode < 200 || $statusCode >= 300) {
    //         $error = $response->getError();
    //         if (isset($error)) {
    //             $response->withData('error', [
    //                 'code' => $error->getCode(),
    //                 'message' => $error->getMessage(),
    //             ]);
    //         } else {
    //             $response->withData('error', [
    //                 'code' => $statusCode,
    //                 'message' => $response->getReasonPhrase(),
    //             ]);
    //         }
    //     }
    //     $body->write(json_encode($response->getData()));
    //     $response = $response->withBody($body);
    //     return $response;
    // }

    public function negotiate(Context $context)
    {
        $contentType = $context->getContentType();
        if ($contentType) {
            return $contentType;
        }

        $ext = $context->getUri()->getExtension();
        if ($ext && $this['accepts'][$ext]) {
            return $this['accepts'][$ext];
        }

        $contentType = $context->getRequest()->getContentType();
        if (!$contentType) {
            $accepted = $context->accepts(array_keys($this['accepts']));
            if (isset($accepted)) {
                $contentType = is_string($this['accepts'][$accepted]) ? $this['accepts'][$accepted] : $accepted;
            }
        }
        if ($contentType) {
            return $this->match($contentType);
        }

    }

    public function match($contentType)
    {
        $matchingTypes = $this->options['matchingTypes'];
        if (is_callable($matchingTypes)) {
            return $matchingTypes($contentType);
        } else {
            return isset($matchingTypes[$contentType]) ? $matchingTypes[$contentType] : $contentType;
        }
    }

    public function __invoke(Context $context, $next = null)
    {
        // avoid content negotiator on cli
        if ($context->getApp()->isCli()) {
            $next($context);
            return;
        }

        // set response content type by negotiating to context
        $contentType = $this->negotiate($context);
        if ($contentType) {
            $context->withContentType($contentType);
        }

        $next($context);

        // render respect response content type
        $contentType = $context->getContentType();
        if (isset($this['renderers'][$contentType])) {
            $handler = $this['renderers'][$contentType]->getHandler();
            $handler($context);
        }
    }
}
