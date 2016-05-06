<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Uri;
use Bono\Middleware\TemplateRenderer;
use Bono\Middleware\StaticPage;
use ROH\Util\Injector;
use ROH\Util\File;

class StaticPageTest extends BonoTestCase
{
    public function setUp()
    {
        parent::setUp();
        @mkdir('./tmp', 0777, true);
    }

    public function tearDown()
    {
        File::rm('./tmp');
        parent::tearDown();
    }

    public function testInvokeWithoutRenderer()
    {
        $request = new Request('GET', new Uri('http', 'localhost'));
        $context = Injector::getInstance()->resolve(Context::class, [ 'request' => $request ]);

        $middleware = new StaticPage();
        $hitNext = false;
        $next = function () use (&$hitNext) {
            $hitNext = true;
        };

        $middleware($context, $next);
        $this->assertEquals(true, $hitNext);
        $this->assertEquals(404, $context->getStatusCode());
    }

    public function testInvokeWithRenderer()
    {
        $request = new Request('GET', new Uri('http', 'localhost'));

        $middleware = new StaticPage();
        $hitNext = false;
        $next = function () use (&$hitNext) {
            $hitNext = true;
        };

        // template found
        $context = Injector::getInstance()->resolve(Context::class, [ 'request' => $request ]);
        $renderer = $context['@renderer'] = $this->getMock(TemplateRenderer::class, [], [$this->app, [
            'renderer' => [],
        ]]);
        $renderer->method('resolve')->will($this->returnValue(true));


        $middleware($context, $next);
        $this->assertEquals(200, $context->getStatusCode());
        $this->assertEquals(false, $hitNext);

        // template not found
        $context = Injector::getInstance()->resolve(Context::class, [ 'request' => $request ]);
        $renderer = $context['@renderer'] = $this->getMock(TemplateRenderer::class, [], [$this->app, [
            'renderer' => [],
        ]]);
        $renderer->method('resolve')->will($this->returnValue(false));

        $middleware($context, $next);
        $this->assertEquals(404, $context->getStatusCode());
        $this->assertEquals(true, $hitNext);
    }
}
