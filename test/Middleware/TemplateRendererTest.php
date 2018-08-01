<?php

namespace Bono\Test\Middleware;

use PHPUnit\Framework\TestCase;
use Bono\Http\Context;
use Bono\Http\Uri;
use Bono\Middleware\TemplateRenderer;
use Bono\Exception\BonoException;
use Bono\Renderer\RendererInterface;
use ROH\Util\Injector;

class TempateRendererTest extends TestCase
{
    public function testConstructWithoutRendererOptionThrowException()
    {
        try {
            new TemplateRenderer(new Injector());
            $this->fail('Must throw Exception');
        } catch (BonoException $e) {
            $this->assertTrue(true);
        }
    }

    public function testInvokeOnContextExceptionThrown()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $context = (new Injector())->resolve(Context::class);
        $middleware($context, function ($context) {
            $context->throwError(412);
        });
        $this->assertTrue(true);
    }


    public function testInvokeOn200AlreadyDefineResponseTemplate()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $context = (new Injector())->resolve(Context::class);
        $middleware($context, function ($context) {
            $context->setStatus(200);
            $context['@renderer.template'] = 'foobar';
        });
        $this->assertEquals('foobar', $context['@renderer.template']);
    }

    public function testInvokeOn200TemplateSetFromRouteInfo()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $context = (new Injector())->resolve(Context::class);
        $context['route.info'] = [1, ['template' => 'foo']];
        $middleware($context, function ($context) {
            $context->setStatus(200);
        });
        $this->assertEquals('foo', $context['@renderer.template']);
    }

    public function testInvokeOn200DetectTemplate()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $context = (new Injector())->resolve(Context::class);
        $context['route.uri'] = new Uri();
        $middleware($context, function ($context) {
            $context->setStatus(200);
        });
        $this->assertEquals('index', $context['@renderer.template']);
    }

    public function testInvokeOn404()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $context = (new Injector())->resolve(Context::class);
        $middleware($context, function ($context) {
            $context->setStatus(404);
        });
        $this->assertEquals('404', $context['@renderer.template']);
    }

    public function testInvokeOn405()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $context = (new Injector())->resolve(Context::class);
        $middleware($context, function ($context) {
            $context->setStatus(405);
        });
        $this->assertEquals('405', $context['@renderer.template']);
    }

    public function testResolve()
    {
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->expects($this->once())->method('resolve');
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $renderer,
        ]);

        $middleware->resolve('foo');
    }

    public function testRender()
    {
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->expects($this->once())->method('render');
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $renderer,
        ]);

        $middleware->render('foo');
    }

    public function testAddTemplatePath()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(RendererInterface::class),
        ]);

        $this->assertEquals(count($middleware['templatePaths']), 1);
        $result = $middleware->addTemplatePath('foo/bar');
        $this->assertEquals($result, $middleware);
        $this->assertEquals(count($middleware['templatePaths']), 2);
    }

    public function testGetRendererThrowExceptionOnRendererNotRendererInterface()
    {
        $middleware = new TemplateRenderer(new Injector(), [
            'renderer' => $this->createMock(\stdClass::class),
        ]);

        try {
            $middleware->resolve('/');
            $this->fail('Should throw exception');
        } catch (BonoException $e) {
            $this->assertEquals($e->getMessage(), 'Renderer must be instance of RendererInterface');
        }
    }
}
