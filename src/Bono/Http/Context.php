<?php
namespace Bono\Http;

use Bono\App;
use Bono\Http\Response;
use ArrayAccess;
use Bono\Exception\ContextException;

class Context implements ArrayAccess
{
    protected $state;

    protected $request;

    protected $response;

    protected $app;

    public function __construct(Request $request, Response $response)
    {
        $this->app = App::getInstance();
        $this->request = $request;
        $this->response = $response;
        $this->state = [];
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getParam($key, $default = null)
    {
        $postParams = $this->getParsedBody();
        if (isset($postParams[$key])) {
            return $postParams[$key];
        }

        $getParams = $this->getQueryParams();
        if (isset($getParams[$key])) {
            return $getParams[$key];
        }

        return $default;
    }

    public function withBundle($bundle)
    {
        $this->bundle = $bundle;
        $this['bundle.uri'] = $this->getUri();
        return $this;
    }

    public function withRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function withMethod($method)
    {
        $this->request = $this->request->withMethod($method);
        return $this;
    }

    public function withState($key, $state = null)
    {
        if (1 == func_num_args()) {
            $this->state = $key;
        } else {
            $this->state[$key] = $state;
        }
        return $this;
    }

    public function handleError($err)
    {
        $this->withContentType('text/plain')
            ->withStatus($err->getStatusCode())
            ->write($err->getMessage());
    }

    public function throwError($status = 500, $message = null, $error = null)
    {
        throw new ContextException($status, $message ?: Response::$messages[$status], $error);
    }

    // delegates to request
    public function getMethod()
    {
        return $this->request->getMethod();
    }

    public function getUri()
    {
        return $this->request->getUri();
    }

    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    public function getQueryParams()
    {
        return $this->request->getQueryParams();
    }

    public function accepts($types)
    {
        return $this->request->accepts($types);
    }

    public function getPathname()
    {
        return $this->request->getUri()->getPathname();
    }

    public function withAttribute($key, $value)
    {
        $this->request = $this->request->withAttribute($key, $value);
        return $this;
    }

    public function withParsedBody($body)
    {
        $this->request = $this->request->withParsedBody($body);
        return $this;
    }

    public function shift($path)
    {
        $this->withRequest($this->getRequest()->shift($path));
        return $this;
    }

    public function unshift($path)
    {
        $this->withRequest($this->getRequest()->unshift($path));
        return $this;
    }

    // delegates to response
    public function getContentType()
    {
        return $this->response->getContentType();
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getBody()
    {
        return $this->response->getBody();
    }

    public function withStatus($status)
    {
        $this->response = $this->response->withStatus($status);
        return $this;
    }

    public function withHeader($key, $value)
    {
        $this->response = $this->response->withHeader($key, $value);
        return $this;
    }

    public function withContentType($contentType)
    {
        return $this->withHeader('Content-Type', $contentType);
    }

    public function write($str)
    {
        $this->getBody()->write($str);

        return $this;
    }

    // arrayaccess
    public function offsetExists($key)
    {
        return $this->request->getAttribute($key);
    }

    public function offsetGet($key)
    {
        return $this->request->getAttribute($key);
    }

    public function offsetSet($key, $value)
    {
        $this->request = $this->request->withAttribute($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->request = $this->request->withoutAttribute($key);
    }

    public function __debugInfo()
    {
        return [
            'request' => $this->getMethod() . ' ' . $this->getPathname(),
            'response' => $this->getStatusCode(),
            'state' => $this->getState(),
        ];
    }
}
