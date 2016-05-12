<?php

namespace Bono\Test\Route;

use PHPUnit_Framework_TestCase;
use Bono\Route\Dispatcher;

class DispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testDispatch()
    {
        $dispatcher = new Dispatcher([
            [
                'methods' => ['GET'],
                'pattern' => '/',
            ],
            [
                'methods' => ['GET'],
                'pattern' => '/foo/{something}',
            ]
        ]);

        $result = $dispatcher->dispatch('GET', '/missing');
        $this->assertEquals($result[0], 0);

        $result = $dispatcher->dispatch('HEAD', '/missing');
        $this->assertEquals($result[0], 0);

        $result = $dispatcher->dispatch('GET', '/');
        $this->assertEquals($result[0], 1);

        $result = $dispatcher->dispatch('HEAD', '/');
        $this->assertEquals($result[0], 1);

        $result = $dispatcher->dispatch('POST', '/');
        $this->assertEquals($result[0], 2);

        $result = $dispatcher->dispatch('GET', '/foo/bar');
        $this->assertEquals($result[0], 1);

        $result = $dispatcher->dispatch('HEAD', '/foo/bar');
        $this->assertEquals($result[0], 1);

        $result = $dispatcher->dispatch('POST', '/foo/bar');
        $this->assertEquals($result[0], 2);
    }
}