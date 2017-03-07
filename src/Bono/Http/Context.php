<?php
namespace Bono\Http;

use Bono\App;
use Bono\Http\Response;
use ArrayAccess;
use Bono\Exception\BonoException;
use Bono\Exception\ContextException;
use Bono\Helper\Url;
use ROH\Util\StringFormatter;
use ROH\Util\Composition;

class Context implements ArrayAccess
{
    protected $state;

    protected $request;

    protected $response;

    protected $app;

    protected $composition;

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

    public function removeParam($key)
    {
        $postParams = $this->getParsedBody();
        unset($postParams[$key]);
        $this->setParsedBody($postParams);
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
        if ($this->response->hasBody()) {
            $this->response->getBody()->close();
        }
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

    public function isRouted()
    {
        return null !== $this['route.info'] && 1 === $this['route.info'][0];
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
        return null !== $uri ? $uri->getPathname() : null;
    }

    public function getUploadedFiles()
    {
        return $this->request->getUploadedFiles();
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

    public function getHeader($key)
    {
        return $this->request->getHeader($key);
    }

    public function getHeaderLine($key)
    {
        return $this->request->getHeaderLine($key);
    }

    public function getCookie($name)
    {
        return $this->request->getCookie($name);
    }

    public function setCookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $this->response = $this->response->withCookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        return $this;
    }

    public function removeCookie($name, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $this->response = $this->response->withoutCookie($name, $path, $domain, $secure, $httponly);
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
        $keys = func_get_args();
        foreach ($keys as $key) {
            if (null === $this[$key]) {
                throw new BonoException('Unregistered dependency ' . $key . ' middleware!');
            }
        }
    }

    public function bundleUrl($uri = '/', $data = [])
    {
        return Url::bundle(StringFormatter::format($uri, $data), $this['route.uri']);
    }

    public function siteUrl($uri = '/', $data = [])
    {
        return Url::bundle(StringFormatter::format($uri, $data), $this['original.uri']);
    }

    public function assetUrl($uri, $data = [])
    {
        return Url::asset(StringFormatter::format($uri, $data), $this['original.uri']);
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

    public function addMiddleware($middleware)
    {
        $this->getComposition()->compose($middleware);
        return $this;
    }

    protected function getComposition()
    {
        if (null === $this->composition) {
            $this->composition = new Composition();
        }
        return $this->composition;
    }

    public function apply(callable $route)
    {
        return $this->getComposition()->setCore($route)->apply($this);
    }

    public function call($middlewareName, $methodName)
    {
        if (null === $this[$middlewareName]) {
            return null;
        }

        $args = array_slice(func_get_args(), 2);
        array_unshift($args, $this);
        return call_user_func_array([$this[$middlewareName], $methodName], $args);
    }

    public function back()
    {
        return $this->redirect($this->getParam('!continue'));
    }

    public function redirect($url = null, $status = 302)
    {
        $url = $url ?: $this->siteUrl();
        $this->setHeader('Location', $url);
        return $this->throwError($status);
    }
}
