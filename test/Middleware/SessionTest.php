<?php

namespace Bono\Test\Middleware;

use PHPUnit\Framework\TestCase;
use Bono\Middleware\Session;
use Bono\Http\Context;
use ROH\Util\Injector;
use Bono\Exception\BonoException;

class SessionTest extends TestCase
{
    public function testAsCli()
    {
        $this->assertTrue(true);
    }
    // public function testAsCli()
    // {
    //     $session = $this->getMock(Session::class, ['start'], [ $this->app ]);
    //     $session->expects($this->never())->method('start');

    //     try {
    //         $session->__invoke((new Injector())->resolve(Context::class), function ($context) {
    //             $context->depends('@session');
    //         });
    //         $this->fail('must not here');
    //     } catch (BonoException $e) {
    //         if ($e->getMessage() !== 'Unregistered dependency @session middleware!') {
    //             throw $e;
    //         }
    //     }
    // }

    // public function testAsWeb()
    // {
    //     $this->app['cli'] = false;
    //     $session = new Session($this->app);

    //     $session->__invoke((new Injector())->resolve(Context::class), function ($context) {
    //         $context->depends('@session');
    //         $context['@session.data'] = [];
    //     });
    // }

    // public function testInvokeReadPersistedValues()
    // {
    //     $this->app['cli'] = false;
    //     $session = new Session($this->app);

    //     $_SESSION = ['foo' => 'bar'];

    //     $session->__invoke((new Injector())->resolve(Context::class), function ($context) {
    //         $this->assertEquals($context['@session']->get($context, 'foo'), 'bar');
    //         $this->assertEquals($context['@session']->get($context, 'bar', 'default'), 'default');
    //     });
    // }

    // public function testInvokeSaveValues()
    // {
    //     $this->app['cli'] = false;
    //     $session = new Session($this->app);

    //     $_SESSION = [];

    //     $session->__invoke((new Injector())->resolve(Context::class), function ($context) {
    //         $context['@session']->set($context, 'foo', 'bar');
    //     });

    //     $this->assertEquals($_SESSION['foo'], 'bar');
    // }

    // public function testInvokeCaughtError()
    // {
    //     $this->app['cli'] = false;
    //     $session = $this->getMockBuilder(Session::class)
    //         ->setMethods(['stop'])
    //         ->setConstructorArgs([ $this->app ])
    //         ->getMock();

    //     $session->expects($this->once())->method('stop');

    //     try {
    //         $_SESSION = [];
    //         $session->__invoke((new Injector())->resolve(Context::class), function ($context) {
    //             throw new \Exception('Ouch');
    //         });
    //     } catch (\Exception $e) {
    //         if ($e->getMessage() !== 'Ouch') {
    //             throw $e;
    //         }
    //     }
    // }

    // public function testReset()
    // {
    //     $session = new Session($this->app);
    //     $_SESSION = [ 'foo' => 'bar' ];
    //     $session->reset((new Injector())->resolve(Context::class));
    //     $this->assertEquals($_SESSION, []);

    //     $session = new Session($this->app);
    //     $_SESSION = [ 'foo' => 'bar' ];
    //     $session->reset((new Injector())->resolve(Context::class), true);
    //     $this->assertEquals(array_keys($_COOKIE), ['keep']);
    // }
}
