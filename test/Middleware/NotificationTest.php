<?php

namespace Bono\Test\Middleware;

use Bono\Test\BonoTestCase;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Stream;
use Bono\Middleware\Notification;
use Bono\Exception\BonoException;

class NotificationTest extends BonoTestCase
{
    public function testInvoke()
    {
        $middleware = new Notification($this->app);

        $context = $this->app->resolve(Context::class, [
            'request' => new Request(),
        ]);
        $middleware($context, function() {});

        $this->assertEquals($context['@notification'], $middleware);
    }

    public function testNotify()
    {
        $middleware = new Notification($this->app);

        $notif = [
            'level' => 'info',
            'message' => 'foo',
        ];

        $middleware->notify($notif);
        $middleware->notify([
            'level' => 'info',
            'message' => 'bar',
            'context' => 'baz',
        ]);

        $result = $middleware->query(['level' => 'info']);
        $this->assertEquals($result[0], $notif);

        $result = $middleware->query(['level' => 'info', 'context' => 'baz']);
        $this->assertEquals($result[0]['message'], 'bar');

        $result = $middleware->render();
        $this->assertTrue(strpos($result, 'foo') > 0);
    }
}