<?php
namespace Bono\Test\Http;

use Bono\Test\BonoTestCase;
use Bono\Http\Response;
use Bono\Http\Stream;
use Bono\Http\Headers;
use Bono\Exception\BonoException;

class ResponseTest extends BonoTestCase
{
    public function testConstruct()
    {
        new Response(200, null, new Stream());
        $response = new Response(200, new Headers(['foo' => 'bar']), 'foo bar');
        $this->assertEquals($response->getHeaderLine('foo'), 'bar');
        $this->assertEquals($response->getBody()->__toString(), 'foo bar');
    }

    public function testWithStatus()
    {
        $response = new Response();
        $response = $response->withStatus(444);
        $this->assertEquals($response->getStatusCode(), 444);
        $this->assertEquals($response->getReasonPhrase(), '');

        try {
            $response = $response->withStatus('404');
            $this->fail('Must not here');
        } catch (BonoException $e) {
            if ($e->getMessage() !== 'Invalid HTTP status code') {
                throw $e;
            }
        }

        try {
            $response = $response->withStatus(404, new \stdClass());
            $this->fail('Must not here');
        } catch (BonoException $e) {
            if ($e->getMessage() !== 'ReasonPhrase must be a string') {
                throw $e;
            }
        }
    }

    public function testWithAddedHeaderAndWithoutHeader()
    {
        $response = new Response();
        $response = $response->withHeader('foo', 'bar');
        $response = $response->withAddedHeader('foo', 'baz');
        $this->assertEquals($response->getHeaderLine('foo'), 'bar, baz');
        $response = $response->withoutHeader('foo');
        $this->assertEquals($response->getHeader('foo'), null);
    }

    public function testProtocolVersion()
    {
        $response = new Response();
        $this->assertEquals($response->getProtocolVersion(), '1.1');
        $this->assertEquals($response->withProtocolVersion('1.0')->getProtocolVersion(), '1.0');
    }
}
