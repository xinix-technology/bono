<?php

namespace Bono\Test\Middleware;

use PHPUnit\Framework\TestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Stream;
use Bono\Middleware\BodyParser;
use Bono\Exception\BonoException;
use ROH\Util\Injector;

class BodyParserTest extends TestCase
{
    public function testInvokeParseForm()
    {
        $middleware = new BodyParser();

        $context = (new Injector())->resolve(Context::class, [
            'request' => new Request('POST'),
        ]);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/x-www-form-urlencoded'));

        $stream = new Stream();
        $stream->write('foo=bar');
        $stream->rewind();
        $context->setRequest($context->getRequest()->withBody($stream));
        $middleware($context, function () {
        });

        $body = $context->getParsedBody();
        $this->assertEquals($body['foo'], 'bar');

        $context = (new Injector())->resolve(Context::class, [
            'request' => new Request('PUT'),
        ]);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/x-www-form-urlencoded'));
    }

    public function testInvokeParseJson()
    {
        $middleware = new BodyParser();

        $fakeBody = new Stream();
        $fakeBody->write(json_encode([
            'foo' => 'bar',
        ]));

        $context = (new Injector())->resolve(Context::class, [
            'request' => new Request('POST'),
        ]);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/json')->withBody($fakeBody));

        // $_POST = ['foo' => 'bar'];
        $middleware($context, function () {
        });

        $body = $context->getParsedBody();
        $this->assertEquals($body['foo'], 'bar');
    }

    public function testInvokeParseUnknown()
    {
        $middleware = new BodyParser();

        $context = (new Injector())->resolve(Context::class, [
            'request' => new Request('POST'),
        ]);

        try {
            $middleware($context, function ($context) {
                $body = $context->getParsedBody();
            });
            $this->fail('Must throw exception');
        } catch (BonoException $e) {
            if (strpos($e->getMessage(), 'Cannot found parser for') !== 0) {
                throw $e;
            }

            $this->assertTrue(true);
        }
    }
}
