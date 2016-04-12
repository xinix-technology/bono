<?php
namespace Bono\Test\Http;

use PHPUnit_Framework_TestCase;
use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Exception\BonoException;

class ContextTest extends BonoTestCase {
    public function testGetAttributes()
    {
        $context = $this->app->resolve(Context::class);

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

    public function testAttributes()
    {
        $context = $this->app->resolve(Context::class);
        $context->setAttribute('foo', 'bar');
        $this->assertEquals($context->getRequest()->getAttribute('foo'), 'bar');

        unset($context['foo']);
        $this->assertEquals($context->getRequest()->getAttribute('foo'), null);
    }

    public function testWrite()
    {
        $context = $this->app->resolve(Context::class);
        $result = $context->write('foo bar');
        $this->assertEquals($result, $context);
    }

    public function testDebugInfo()
    {
        $context = $this->app->resolve(Context::class);
        $info = $context->__debugInfo();
        $this->assertEquals($info['request'], 'GET /');
        $this->assertEquals($info['response'], 404);
    }

    public function testBundleAndSiteUrl()
    {
        $context = $this->app->resolve(Context::class);
        $this->assertEquals($context->bundleUrl('/foo'), 'http://127.0.0.1:80/foo');
        $this->assertEquals($context->siteUrl('/foo'), 'http://127.0.0.1:80/foo');
        $context->shift('/bar');
        $this->assertEquals($context->bundleUrl('/foo'), 'http://127.0.0.1:80/bar/foo');
        $this->assertEquals($context->siteUrl('/foo'), 'http://127.0.0.1:80/foo');
    }
}