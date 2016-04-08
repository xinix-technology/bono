<?php

namespace Bono\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected $protocolVersion = '1.1';

    protected $headers;

    protected $body;

    protected $contentType = false;

    public function __construct($headers = null)
    {
        $this->headers = new Headers($headers);
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
        throw new \Exception('Unimplemented yet!');

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
        throw new \Exception('Unimplemented yet!');

    }

    public function withoutHeader($name)
    {
        throw new \Exception('Unimplemented yet!');

    }

    public function getBody()
    {
        return $this->body;

    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Test for invalid body?
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
