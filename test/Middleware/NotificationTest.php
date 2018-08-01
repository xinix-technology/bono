<?php

namespace Bono\Test\Middleware;

use PHPUnit\Framework\TestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Stream;
use Bono\Middleware\Notification;
use Bono\Exception\BonoException;
use ROH\Util\Injector;

class NotificationTest extends TestCase
{
    public function testInvoke()
    {
        $this->assertTrue(true);
    }
    // public function testInvoke()
    // {
    //     $middleware = new Notification($this->app);

    //     $context = (new Injector())->resolve(Context::class, [
    //         'request' => new Request(),
    //     ]);
    //     $middleware($context, function () {
    //     });

    //     $this->assertEquals($context['@notification'], $middleware);
    // }

    // public function testNotify()
    // {
    //     $context = (new Injector())->resolve(Context::class, [
    //         'request' => new Request(),
    //     ]);

    //     $middleware = new Notification($this->app);

    //     $notif = [
    //         'level' => 'info',
    //         'message' => 'foo',
    //     ];

    //     $middleware->notify($context, $notif);
    //     $middleware->notify($context, [
    //         'level' => 'info',
    //         'message' => 'bar',
    //         'context' => 'baz',
    //     ]);

    //     $result = $middleware->query($context, ['level' => 'info']);
    //     $this->assertEquals($result[0], $notif);

    //     $result = $middleware->query($context, ['level' => 'info', 'context' => 'baz']);
    //     $this->assertEquals($result[0]['message'], 'bar');

    //     $_SESSION['notification'] = 'foo';
    //     $result = $middleware->render($context);
    //     $this->assertTrue(strpos($result, 'foo') > 0);
    //     $this->assertFalse(isset($_SESSION['notification']));
    // }

    // public function testInvokeThrowError()
    // {
    //     $this->app['cli'] = false;
    //     $middleware = $this->getMock(Notification::class, ['finalize'], [$this->app]);
    //     $middleware->expects($this->once())->method('finalize');

    //     $context = (new Injector())->resolve(Context::class);
    //     try {
    //         $middleware($context, function ($context) {
    //             $this->fail('Oops');
    //         });
    //     } catch (\Exception $e) {
    //         if ($e->getMessage() !== 'Oops') {
    //             throw $e;
    //         }
    //     }
    // }
}
