<?php

namespace Bono\Test\Middleware;

use PHPUnit_Framework_TestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Http\Uri;

use Bono\Middleware\StaticPage;

class StaticPageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        @mkdir('./tmp', 0777, true);
    }

    public function tearDown()
    {
        @rmdir('./tmp');
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

        $context['renderer'] = [
            'templatePath' => './tmp'
        ];
        $middleware($context, $next);
        $this->assertEquals(200, $context->getStatusCode());
        $this->assertEquals('/index', $context['template']);

        unlink($templateFile);
    }
}
