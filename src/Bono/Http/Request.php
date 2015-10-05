<?php
namespace Bono\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use InvalidArgumentException;
use ArrayAccess;

class Request extends Message implements ServerRequestInterface, ArrayAccess
{
    protected static $instance;

    protected $method;

    protected $originalMethod;

    protected $uri;

    protected $accepts;

    protected $parsedBody;

    protected $queryParams;

    public static function getInstance($cli = false)
    {
        if (!isset(static::$instance)) {
            $method = $cli ? 'GET' : $_SERVER['REQUEST_METHOD'];
            $request = new Request($method, Uri::getInstance($cli));
            $request->headers = Headers::getInstance();
            static::$instance = $request;
        }

        return static::$instance;
    }

    public function __construct($method = 'GET', $uri = null)
    {
        $this->method = $method;
        $this->originalMethod = $method;
        $this->uri = $uri;

        parent::__construct();
    }

    // custom

    public function getParam($key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $getParams = $this->getQueryParams();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    public function getOriginalMethod()
    {
        return $this->originalMethod;
    }

    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }

    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }

    public function isPut()
    {
        return $this->getMethod() === 'PUT';
    }

    public function isDelete()
    {
        return $this->getMethod() === 'DELETE';
    }

    public function isOptions()
    {
        return $this->getMethod() === 'OPTIONS';
    }

    public function isPatch()
    {
        return $this->getMethod() === 'PATCH';
    }

    public function accept($contentTypes)
    {
        if (!is_array($contentTypes)) {
            $contentTypes = [$contentTypes];
        }

        if (is_null($this->accepts)) {
            $this->accepts = array_map(function ($accept) {
                return explode(';', $accept)[0];
            }, $this->getHeader('accept') ?: []);
        }

        if (in_array('*/*', $this->accepts)) {
            foreach ($contentTypes as $contentType) {
                if (strpos($contentType, '/') === false) {
                    continue;
                }
                return $contentType;
            }
        }

        foreach ($contentTypes as $type) {
            if (in_array($type, $this->accepts)) {
                return $type;
            }
        }

    }

    public function shift($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Uri path must be string');
        }

        return $this->withUri($this->getUri()->shift($path));
    }

    // request interface

    public function getRequestTarget()
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function withRequestTarget($requestTarget)
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);

        return $clone;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            // if ($uri->getHost() !== '') {
            //     $clone->headers->set('Host', $uri->getHost());
            // }
        } else {
            throw new \Exception('Unimplemented yet');
            // if ($this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null)) {
            //     $clone->headers->set('Host', $uri->getHost());
            // }
        }

        return $clone;
    }

    // server request interface
    public function getServerParams()
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function getCookieParams()
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function withCookieParams(array $cookies)
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function getQueryParams()
    {
        if ($this->queryParams) {
            return $this->queryParams;
        }

        if ($this->uri === null) {
            return [];
        }

        parse_str($this->uri->getQuery(), $this->queryParams); // <-- URL decodes data

        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    public function getUploadedFiles()
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException('Parsed body value must be an array, an object, or null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }
}
