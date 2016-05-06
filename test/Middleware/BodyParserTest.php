<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Stream;
use Bono\Middleware\BodyParser;
use Bono\Exception\BonoException;
use ROH\Util\Injector;

class BodyParserTest extends BonoTestCase
{
    public function testInvokeParseForm()
    {
        $middleware = new BodyParser();

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('POST'),
        ]);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/x-www-form-urlencoded'));

        $context->getRequest()->getBody()->write('foo=bar');
        $middleware($context, function() {

        });

        $body = $context->getParsedBody();
        $this->assertEquals($body['foo'], 'bar');

        $context = Injector::getInstance()->resolve(Context::class, [
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

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('POST'),
        ]);
        $context->setRequest($context->getRequest()->withHeader('Content-Type', 'application/json')->withBody($fakeBody));

        // $_POST = ['foo' => 'bar'];
        $middleware($context, function() {

        });

        $body = $context->getParsedBody();
        $this->assertEquals($body['foo'], 'bar');
    }

    public function testInvokeParseUnknown()
    {
        $middleware = new BodyParser();

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('POST'),
        ]);

        try {
            $middleware($context, function($context) {
                $body = $context->getParsedBody();
            });
            $this->fail('Must throw exception');
        } catch(BonoException $e) {
            if (strpos($e->getMessage(), 'Cannot found parser for') !== 0) {
                throw $e;
            }
        }
    }
}