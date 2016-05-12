<?php
namespace Bono\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Bono\Exception\BonoException;
use ROH\Util\Collection;

class Request extends Message implements ServerRequestInterface
{
    private static $shortTypes = [
        'html' => ['text/html'],
        'json' => ['application/json'],
    ];

    protected $method;

    protected $originalMethod;

    protected $uri;

    protected $accepts;

    protected $parsedBody;

    protected $serverParams;

    protected $cookieParams;

    protected $queryParams;

    protected $requestTarget;

    protected $attributes;

    protected $uploadedFiles = [];

    public function __construct($method = 'GET', Uri $uri = null, Headers $headers = null)
    {
        $this->method = $method;
        $this->originalMethod = $method;
        $this->uri = $uri ?: new Uri();
        $this->attributes = new Collection([
            'original.uri' => $this->uri,
            'route.uri' => $this->uri,
        ]);

        parent::__construct($headers);
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

    private function normalizeTypeArrays($types)
    {
        $normalized = [];
        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            if (false === strpos($type, '/')) {
                if (isset(static::$shortTypes[$type])) {
                    $normalized += static::$shortTypes[$type];
                }
            } else {
                $normalized[] = $type;
            }
        }

        return $normalized;
    }

    public function accepts($types)
    {
        $types = $this->normalizeTypeArrays($types);

        if (null === $this->accepts) {
            $this->accepts = array_map(function ($accept) {
                return explode(';', $accept)[0];
            }, $this->getHeader('accept') ?: []);
        }

        $acceptAny = in_array('*/*', $this->accepts);
        foreach ($types as $type) {
            if ($acceptAny || in_array($type, $this->accepts)) {
                return $type;
            }
        }
    }

    public function shift($path)
    {
        $uri = $this->getUri()->shift($path);
        $this->attributes['route.uri'] = $uri;
        return $this->withUri($uri);

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
     * withAttribute
     * @param  [type] $name  [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * withoutAttribute
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $this;
    }

    public function getRequestTarget()
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }
        // if ($this->uri === null) {
        //     return '/';
        // }
        $basePath = $this->uri->getBasePath();
        $path = $this->uri->getPath();
        $query = $this->uri->getQuery();

        $path = $basePath . '/' . ltrim($path, '/') . ($query ? '?' . $query : '');
        $this->requestTarget = $path;
        return $this->requestTarget;
    }

    public function withRequestTarget($requestTarget)
    {
        // if (preg_match('#\s#', $requestTarget)) {
        //     throw new InvalidArgumentException(
        //         'Invalid request target provided; must be a string and cannot contain whitespace'
        //     );
        // }
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
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

        if (!empty($uri->getHost())) {
            if ($preserveHost) {
                if (!$this->hasHeader('Host') || $this->getHeader('Host') === null) {
                    $clone->headers['Host'] = $uri->getHost();
                }
            } else {
                $clone->headers['Host'] = $uri->getHost();
            }
        }

        return $clone;
    }

    // server request interface
    public function getServerParams()
    {
        if (null === $this->serverParams) {
            $this->serverParams = array_merge([], $_SERVER);
        }
        return $this->serverParams;
    }

    public function getCookieParams()
    {
        if (null === $this->cookieParams) {
            $this->cookieParams = array_merge([], $_COOKIE);
        }
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams()
    {
        if (null == $this->queryParams) {
            parse_str($this->uri->getQuery(), $this->queryParams); // <-- URL decodes data
        }
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
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        if (null !== $data && !is_object($data) && !is_array($data)) {
            throw new BonoException('Parsed body value must be an array, an object, or null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    public function getBody()
    {
        if (null === $this->body) {
            $stream = fopen('php://temp', 'w+');
            stream_copy_to_stream(fopen('php://input', 'r'), $stream);
            rewind($stream);
            $this->body = new Stream($stream);
        }

        return $this->body;
    }
}
