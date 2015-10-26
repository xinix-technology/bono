<?php
namespace Bono\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use InvalidArgumentException;
use ROH\Util\Collection;

class Request extends Message implements ServerRequestInterface
{
    protected static $instance;

    protected $method;

    protected $originalMethod;

    protected $uri;

    protected $accepts;

    protected $parsedBody;

    protected $queryParams;

    protected $attributes;

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
        $this->attributes = new Collection();

        parent::__construct();
    }

    // custom

    public function getOriginalMethod()
    {
        return $this->originalMethod;
    }

    // public function isGet()
    // {
    //     return $this->getMethod() === 'GET';
    // }

    // public function isPost()
    // {
    //     return $this->getMethod() === 'POST';
    // }

    // public function isPut()
    // {
    //     return $this->getMethod() === 'PUT';
    // }

    // public function isDelete()
    // {
    //     return $this->getMethod() === 'DELETE';
    // }

    // public function isOptions()
    // {
    //     return $this->getMethod() === 'OPTIONS';
    // }

    // public function isPatch()
    // {
    //     return $this->getMethod() === 'PATCH';
    // }

    public function accepts($types)
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        if (is_null($this->accepts)) {
            $this->accepts = array_map(function ($accept) {
                return explode(';', $accept)[0];
            }, $this->getHeader('accept') ?: []);
        }

        if (in_array('*/*', $this->accepts)) {
            foreach ($types as $contentType) {
                if (strpos($contentType, '/') === false) {
                    continue;
                }
                return $contentType;
            }
        }

        foreach ($types as $type) {
            if (in_array($type, $this->accepts)) {
                return $type;
            }
        }

    }

    public function shift($path)
    {
        return $this->withUri($this->getUri()->shift($path));
    }

    public function unshift($path)
    {
        return $this->withUri($this->getUri()->unshift($path));
    }

    // request interface

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Mutable withAttribute
     * @param  [type] $name  [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Mutable withoutAttribute
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function withoutAttribute($name)
    {
        unset($this->attributes[$name]);

        return $this;
    }

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
