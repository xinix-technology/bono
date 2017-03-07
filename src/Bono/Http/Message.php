<?php

namespace Bono\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    protected $protocolVersion = '1.1';

    protected $headers;

    protected $body;

    protected $contentType = false;

    protected $cookies;

    public function __construct(Headers $headers = null)
    {
        $this->headers = $headers ?: new Headers();
    }

    public function withCookies(Cookies $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;
        return $clone;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function getContentType()
    {
        if ($this->contentType === false) {
            $result = $this->getHeader('Content-Type');
            $result =  $result ? $result[0] : null;
            $this->contentType = explode(';', $result, 2)[0] ?: null;
        }

        return $this->contentType;
    }

    // message interface

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version)
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);

    }

    public function getHeader($name)
    {
        return $this->headers[$name];
    }

    public function getHeaderLine($name)
    {
        return implode(', ', $this->headers[$name] ?: []);
    }

    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        $clone->headers->add($name, $value);
        return $clone;
    }

    public function withoutHeader($name)
    {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    public function hasBody()
    {
        return null !== $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Test for invalid body?
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
