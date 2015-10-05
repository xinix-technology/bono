<?php

namespace Bono\Middleware;

use Bono\Http\Stream;
use ROH\Util\Thing;
use ROH\Util\Options;
use Bono\App;

class ContentNegotiator
{
    protected $options;

    protected $renderers = [];

    public function __construct($options = [])
    {
        $this->options = Options::create([
                'renderers' => [
                    'application/json' => [$this, 'renderJson'],
                    'text/html' => [$this, 'renderHtml'],
                ],
                'responseContentTypes' => [
                    'application/x-www-form-urlencoded' => 'text/html',
                    'multipart/form-data' => 'text/html'
                ],
                'accepts' => [],
            ], App::getInstance()->getOption('env'))
            ->merge($options);
            // ->toArray();

        foreach ($this->options['renderers'] as $key => $renderer) {
            $this->renderers[$key] = new Thing($renderer);
        }
    }

    public function renderJson($response)
    {
        $body = new Stream();
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $error = $response->getError();
            if (isset($error)) {
                $response->withData('error', [
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                ]);
            } else {
                $response->withData('error', [
                    'code' => $statusCode,
                    'message' => $response->getReasonPhrase(),
                ]);
            }
        }
        $body->write(json_encode($response->getData()));
        $response = $response->withBody($body);
        return $response;
    }

    public function renderHtml($response)
    {
        return $response;
    }

    public function negotiate($request)
    {
        $contentType = $request->getContentType();
        if ($contentType && $request->accept($contentType)) {
            return $contentType;
        }

        $extension = $request->getUri()->getExtension();
        if (isset($extension) && isset($this->options['accepts'][$extension])) {
            return $this->options['accepts'][$extension];
        }
        return $request->accept(array_keys($this->options['accepts']));
    }

    public function render($response, $contentType)
    {
        if (!isset($this->renderers[$contentType])) {
            throw new \Exception('Cannot found renderer for '.$contentType);
        }
        $handler = $this->renderers[$contentType]->getHandler();
        return $handler($response)->withHeader('Content-Type', $contentType);
    }

    public function resolveResponseContentType($contentType)
    {
        $responseContentTypes = $this->options['responseContentTypes'];
        if (is_callable($responseContentTypes)) {
            return $responseContentTypes($contentType);
        } else {
            return isset($responseContentTypes[$contentType]) ? $responseContentTypes[$contentType] : $contentType;
        }
    }

    public function __invoke($request, $next = null)
    {
        if (App::getInstance()->isCli()) {
            $response = $next($request);
        } else {
            $contentType = $this->negotiate($request);
            if ($contentType) {
                $responseContentType = $this->resolveResponseContentType($contentType);
                $response = $this->render(
                    $next($request->withHeader('Response-Content-Type', $responseContentType)),
                    $responseContentType
                );
            }
        }
        return $response;

    }
}
