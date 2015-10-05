<?php

namespace Bono\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use ROH\Util\Collection;

class Message implements MessageInterface
{
    protected $protocolVersion = '1.1';

    protected $headers;

    protected $body;

    protected $attributes;

    public function __construct($headers = null)
    {
        $this->attributes = new Collection();
        $this->headers = new Headers($headers);
    }

    public function getContentType()
    {
        $result = $this->getHeader('Content-Type');

        return $result ? $result[0] : null;
    }

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

    /**
     * Get the value of attributes based on offset.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->attributes[$key];
        }
    }

    /**
     * Set a value of an attributes.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Determine if attribute exist by the offset name.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Remove an attributes value by the offset name.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->attributes[$key]);
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
