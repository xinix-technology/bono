<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Uri;
use Bono\Middleware\TemplateRenderer;
use Bono\Middleware\StaticPage;

class StaticPageTest extends BonoTestCase
{
    public function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function setUp()
    {
        parent::setUp();
        @mkdir('./tmp', 0777, true);
    }

    public function tearDown()
    {
        $this->deleteDir('./tmp');
        parent::tearDown();
    }

    public function testInvokeWithoutRenderer()
    {
        $request = new Request('GET', new Uri('http', 'localhost'));
        $context = $this->app->resolve(Context::class, [ 'request' => $request ]);

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
        $context = $this->app->resolve(Context::class, [ 'request' => $request ]);
        $renderer = $context['@renderer'] = $this->getMock(TemplateRenderer::class, [], [$this->app, [
            'renderer' => [],
        ]]);
        $renderer->method('resolve')->will($this->returnValue(true));


        $middleware($context, $next);
        $this->assertEquals(200, $context->getStatusCode());
        $this->assertEquals(false, $hitNext);

        // template not found
        $context = $this->app->resolve(Context::class, [ 'request' => $request ]);
        $renderer = $context['@renderer'] = $this->getMock(TemplateRenderer::class, [], [$this->app, [
            'renderer' => [],
        ]]);
        $renderer->method('resolve')->will($this->returnValue(false));

        $middleware($context, $next);
        $this->assertEquals(404, $context->getStatusCode());
        $this->assertEquals(true, $hitNext);
    }
}
