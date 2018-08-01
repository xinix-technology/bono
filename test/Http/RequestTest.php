<?php
namespace Bono\Test\Http;

use PHPUnit\Framework\TestCase;
use Bono\Http\Request;
use Bono\Exception\BonoException;

class RequestTest extends TestCase
{
    public function setUp()
    {
        $_COOKIE = [];
    }

    public function testAccepts()
    {
        $request = (new Request())
            ->withHeader('Accept', 'text/html');
        $this->assertEquals($request->accepts('text/html'), 'text/html');
        $this->assertNull($request->accepts('application/json'));

        $request = (new Request())
            ->withHeader('Accept', '*/*');
        $this->assertEquals($request->accepts('text/xml'), 'text/xml');

        $request = (new Request())
            ->withHeader('Accept', '*/*');
        $this->assertEquals($request->accepts('json'), 'application/json');
    }

    public function testWithUri()
    {
        $request = new Request();
        $uri = $request->getUri()->withPath('/foo');
        $result = $request->withUri($uri);
        $this->assertEquals($request->getHeaderLine('Host'), '127.0.0.1');

        $request = new Request();
        $uri = $request->getUri()->withPath('/foo');
        $result = $request->withUri($uri, true);
        $this->assertEquals($request->getHeaderLine('Host'), '127.0.0.1');

        $request = new Request();
        $uri = $request->getUri()->withPath('/foo')->withHost(null);
        $result = $request->withUri($uri, true);
        $this->assertEquals($request->getHeaderLine('Host'), null);
    }

    public function testGetServerParams()
    {
        $request = new Request();
        $params = $request->getServerParams();
        $this->assertEquals($params, $_SERVER);
    }

    public function testGetCookieParams()
    {
        $_COOKIE['foo'] = 'bar';

        $request = new Request();
        $params = $request->getCookieParams();
        $this->assertEquals($params['foo'], 'bar');
    }

    public function testWithCookieParams()
    {
        $request = new Request();
        $new = $request->withCookieParams([
            'foo' => 'bar',
        ]);
        $params = $request->getCookieParams();
        $this->assertEquals($params, []);

        $params = $new->getCookieParams();
        $this->assertEquals($params, [
            'foo' => 'bar'
        ]);
    }

    public function testGetQueryParams()
    {
        $request = new Request();
        $request = $request->withUri($request->getUri()->withQuery('foo=bar'));
        $params = $request->getQueryParams();
        $this->assertEquals($params['foo'], 'bar');
    }

    public function testWithQueryParams()
    {
        $request = new Request();
        $params = $request->withQueryParams(['foo' => 'bar'])->getQueryParams();
        $this->assertEquals($params['foo'], 'bar');
    }

    public function testWithParsedBody()
    {
        $request = new Request();
        $request = $request->withParsedBody([]);
        $this->assertEquals($request->getParsedBody(), []);

        try {
            $request = $request->withParsedBody(3333);
            $this->fail('Must not here');
        } catch (BonoException $e) {
        }
    }

    public function testGetBody()
    {
        $request = new Request();
        $body = $request->getBody();
        $this->assertNotNull($body);
    }

    public function testGetRequestTarget()
    {
        $request = new Request();
        $result = $request->getRequestTarget();
        $this->assertEquals($result, '/');
        $this->assertEquals($result, $request->getRequestTarget());

        $request = $request->withRequestTarget('/foo');
        $this->assertEquals($request->getRequestTarget(), '/foo');
    }

    public function testGetUploadedFiles()
    {
        $request = new Request();
        $this->assertEquals($request->getUploadedFiles(), []);

        $request = $request->withUploadedFiles([
            'foo' => [
                0 => [
                    'name' => 'file0.txt',
                    'type' => 'text/plain',
                ],
                1 => [
                    'name' => 'file1.html',
                    'type' => 'text/html',
                ],
            ],
        ]);

        $this->assertEquals($request->getUploadedFiles()['foo'][0]['name'], 'file0.txt');
        $this->assertEquals($request->getUploadedFiles()['foo'][1]['name'], 'file1.html');
    }
}
