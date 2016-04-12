<?php
namespace Bono\Test\Http;

use PHPUnit_Framework_TestCase;
use Bono\Http\Uri;
use Bono\Exception\BonoException;

class UriTest extends PHPUnit_Framework_TestCase {
    public function testDefaultUri()
    {
        $uri = new Uri();
        $this->assertEquals($uri->getHost(), '127.0.0.1');
    }

    public function testByEnvironmentWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.example.net';
        $uri = Uri::byEnvironment($_SERVER);
        $this->assertEquals($uri->getHost(), 'www.example.net');
    }

    public function testByEnvironmentWithHost()
    {
        $_SERVER['HTTP_HOST'] = 'www.example.net';
        $uri = Uri::byEnvironment($_SERVER);
        $this->assertEquals($uri->getHost(), 'www.example.net');
        $this->assertEquals($uri->getPort(), 80);

        $_SERVER['HTTP_HOST'] = 'www.example.net:8080';
        $uri = Uri::byEnvironment($_SERVER);
        $this->assertEquals($uri->getHost(), 'www.example.net');
        $this->assertEquals($uri->getPort(), 8080);

    }

    public function testByEnvironmentWithServerName()
    {
        $_SERVER['SERVER_NAME'] = 'www.example.net';
        $uri = Uri::byEnvironment($_SERVER);
        $this->assertEquals($uri->getHost(), 'www.example.net');
    }

    public function testByEnvironmentWithScriptName()
    {
        $_SERVER['SCRIPT_NAME'] = '/main.php';
        $_SERVER['REQUEST_URI'] = '/main.php/foo/bar';
        $uri = Uri::byEnvironment($_SERVER);
        $this->assertEquals($uri->getBasePath(), '/main.php');
        $this->assertEquals($uri->getPathname(), '/foo/bar');
    }

    public function testByEnvironmentWithQuery()
    {
        $_SERVER['QUERY_STRING'] = 'foo=bar';
        $uri = Uri::byEnvironment($_SERVER);
        $this->assertEquals($uri->getQuery(), 'foo=bar');
    }

    public function testWithPathname()
    {
        $uri = Uri::byEnvironment($_SERVER);
        $uri = $uri->withPathname('/foo/bar');
        $this->assertEquals($uri->getPathname(), '/foo/bar');

        try {
            $uri = $uri->withPathname(80);
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Uri pathname must be a string') {
                throw $e;
            }
        }
    }

    public function testShiftWithExtension()
    {
        $uri = Uri::byEnvironment($_SERVER);
        $uri = $uri->withPath('/user.json');

        $uri = $uri->shift('/user');
        $this->assertEquals($uri->getBasePath(), '/user');
        $this->assertEquals($uri->getPathname(), '');
    }

    public function testFilterScheme()
    {
        try {
            $uri = new Uri(33);
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Uri scheme must be a string') {
                throw $e;
            }
        }

        try {
            $uri = new Uri('some-scheme');
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if (strpos($e->getMessage(), 'Uri scheme must be one of') !== 0) {
                throw $e;
            }
        }
    }

    public function testFilterPort()
    {
        try {
            $uri = new Uri('', '', 'unknown-port');
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if (strpos($e->getMessage(), 'Uri port must be null or an integer') !== 0) {
                throw $e;
            }
        }
    }

    public function testWithBasePath()
    {
        $uri = Uri::byEnvironment($_SERVER);
        try {
            $uri = $uri->withBasePath(80);
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Uri path must be a string') {
                throw $e;
            }
        }
    }

    public function testWithPath()
    {
        $uri = Uri::byEnvironment($_SERVER);
        try {
            $uri = $uri->withPath(80);
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Uri path must be a string') {
                throw $e;
            }
        }
    }

    public function testWithQuery()
    {
        $uri = Uri::byEnvironment($_SERVER);
        try {
            $uri = $uri->withQuery(80);
            $this->fail('Uncaught end');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Query string must be a string') {
                throw $e;
            }
        }
    }
}