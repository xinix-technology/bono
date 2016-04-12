<?php
namespace Bono\Http;

use Bono\App;
use Bono\Http\Response;
use ArrayAccess;
use Bono\Exception\BonoException;
use Bono\Exception\ContextException;
use Bono\Helper\Url;

class Context implements ArrayAccess
{
    protected $state;

    protected $request;

    protected $response;

    protected $app;

    public function __construct(App $app, Request $request, Response $response)
    {
        $this->app = $app;
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

    public function getAttributes()
    {
        return $this->getRequest()->getAttributes();
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

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function setMethod($method)
    {
        $this->request = $this->request->withMethod($method);
        return $this;
    }

    public function setState($key, $state = null)
    {
        if (1 == func_num_args()) {
            $this->state = $key;
        } else {
            $this->state[$key] = $state;
        }
        return $this;
    }

    public function setBody($body)
    {
        $this->response = $this->response->withBody($body);
        return $this;
    }

    // public function handleError($err)
    // {
    //     $this->setContentType('text/plain')
    //         ->setStatus($err->getStatusCode())
    //         ->write($err->getMessage());
    // }

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
        $uri = $this->getUri();
        return isset($uri) ? $uri->getPathname() : null;
    }

    public function setAttribute($key, $value)
    {
        $this->request = $this->request->withAttribute($key, $value);
        return $this;
    }

    public function setParsedBody($body)
    {
        $this->request = $this->request->withParsedBody($body);
        return $this;
    }

    public function shift($path)
    {
        $this->setRequest($this->getRequest()->shift($path));
        return $this;
    }

    public function unshift($path)
    {
        $this->setRequest($this->getRequest()->unshift($path));
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

    public function setStatus($status)
    {
        $this->response = $this->response->withStatus($status);
        return $this;
    }

    public function setHeader($key, $value)
    {
        $this->response = $this->response->withHeader($key, $value);
        return $this;
    }

    public function setContentType($contentType)
    {
        return $this->setHeader('Content-Type', $contentType);
    }

    public function write($str)
    {
        $this->getBody()->write($str);

        return $this;
    }

    public function depends($key)
    {
        if (is_null($this[$key])) {
            throw new BonoException('Unregistered ' . $key . ' middleware!');
        }
    }

    public function bundleUrl($uri)
    {
        return Url::bundle($uri, $this['route.uri']);
    }

    public function siteUrl($uri)
    {
        return Url::bundle($uri, $this['original.uri']);
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
