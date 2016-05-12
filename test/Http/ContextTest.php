<?php
namespace Bono\Test\Http;

use PHPUnit_Framework_TestCase;
use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Http\Headers;
use Bono\Exception\BonoException;
use Bono\Exception\ContextException;
use ROH\Util\Injector;

class ContextTest extends BonoTestCase {
    public function testGetAttributes()
    {
        $context = Injector::getInstance()->resolve(Context::class);

        $context['foo'] = 'bar';

        $attributes = $context->getAttributes();
        $reqAttributes = $context->getRequest()->getAttributes();

        foreach ($attributes as $key => $value) {
            $this->assertEquals($attributes[$key], $reqAttributes[$key]);
        }
    }

    public function testGetParam()
    {
        $context = $this->getMock(Context::class, ['getParsedBody'], [
            $this->app,
            new Request(),
            new Response()
        ]);

        $context->method('getParsedBody')->will($this->returnValue([
            'post-foo' => 'post-bar',
        ]));

        $result = $context->getParam('foo', 'baz');
        $this->assertEquals($result, 'baz');

        $result = $context->getParam('post-foo');
        $this->assertEquals($result, 'post-bar');
    }

    public function testGetHeader()
    {
        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => (new Request())->withHeader('foo', 'bar')
        ]);

        $this->assertEquals($context->getHeader('foo'), ['bar']);
        $this->assertEquals($context->getHeaderLine('foo'), 'bar');
    }

    public function testAttributes()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $context->setAttribute('foo', 'bar');
        $this->assertEquals($context->getRequest()->getAttribute('foo'), 'bar');

        unset($context['foo']);
        $this->assertEquals($context->getRequest()->getAttribute('foo'), null);
    }

    public function testWrite()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $result = $context->write('foo bar');
        $this->assertEquals($result, $context);
    }

    public function testDebugInfo()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $info = $context->__debugInfo();
        $this->assertEquals($info['request'], 'GET /');
        $this->assertEquals($info['response'], 404);
    }

    public function testBundleAndSiteAndAssetUrl()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $this->assertEquals($context->bundleUrl('/foo'), 'http://127.0.0.1:80/foo');
        $this->assertEquals($context->siteUrl('/foo'), 'http://127.0.0.1:80/foo');
        $this->assertEquals($context->assetUrl('/foo'), 'http://127.0.0.1:80/foo');
        $context->shift('/bar');
        $this->assertEquals($context->bundleUrl('/foo'), 'http://127.0.0.1:80/bar/foo');
        $this->assertEquals($context->siteUrl('/foo'), 'http://127.0.0.1:80/foo');
        $this->assertEquals($context->assetUrl('/foo'), 'http://127.0.0.1:80/foo');
    }

    public function testContextMiddleware()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $context->addMiddleware(function($context, $next) use (&$hits) {
            $hits .= '1';
            $next($context);
            $hits .= '3';
        });
        $context->apply(function() use (&$hits) {
            $hits .= '2';
        });

        $this->assertEquals($hits, '123');
    }

    public function testCall()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $context['@foo'] = $this->getMock(\stdClass::class, ['bar']);
        $context['@foo']->expects($this->once())->method('bar');

        $context->call('@foo', 'bar');
    }

    public function testIsRouted()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        $this->assertEquals(false, $context->isRouted());
    }

    public function testBackAndRedirect()
    {
        $context = Injector::getInstance()->resolve(Context::class);
        try {
            $context->back();
            $this->fail('Must not here');
        } catch (ContextException $e) {
            $this->assertEquals($context->getResponse()->getHeaderLine('Location'), $context->siteUrl());
            $this->assertEquals($e->getCode(), 302);
        }

        try {
            $context->redirect('/foo', 301);
            $this->fail('Must not here');
        } catch (ContextException $e) {
            $this->assertEquals($context->getResponse()->getHeaderLine('Location'), '/foo');
            $this->assertEquals($e->getCode(), 301);
        }
    }
}