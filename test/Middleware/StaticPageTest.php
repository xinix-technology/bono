<?php

namespace Bono\Test\Middleware;

use PHPUnit_Framework_TestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Http\Uri;
use Bono\Middleware\TemplateRenderer;

use Bono\Middleware\StaticPage;
use ROH\Util\Injector;

class StaticPageTest extends PHPUnit_Framework_TestCase
{
    public $injector;

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
        @mkdir('./tmp', 0777, true);
        $this->injector = new Injector();
    }

    public function tearDown()
    {
        $this->deleteDir('./tmp');
    }

    public function testInvoke()
    {
        $templateFile = getcwd().'/tmp/index.php';
        file_put_contents($templateFile, '');

        $request = new Request('GET', new Uri('http', 'localhost'));
        $response = new Response(404);
        $context = new Context($request, $response);

        $middleware = new StaticPage([
            'prefix' => '',
        ]);
        $next = function () {

        };

        $middleware($context, $next);
        $this->assertEquals(404, $context->getStatusCode());

        $stub = $this->getMockBuilder(TemplateRenderer::class)->getMock();

        var_dump($stub);
        exit;

        $context['response.renderer'] = [
            'templatePath' => './tmp'
        ];
        $middleware($context, $next);
        $this->assertEquals(200, $context->getStatusCode());
        $this->assertEquals('/index', $context['response.template']);

        unlink($templateFile);
    }
}
