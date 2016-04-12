<?php
namespace Bono\Test;

use Bono\App;

class AppTest extends BonoTestCase
{
    public function testStaticGetInstance()
    {
        $app = App::getInstance();
        $this->assertEquals($app, App::getInstance());
    }

    public function testIsCli()
    {
        $this->assertEquals($this->app->isCli(), true);

        $app = new App([ 'cli' => false ]);
        $this->assertEquals($app->isCli(), false);
    }

    public function testCreateContext()
    {
        $context = $this->app->createContext();

        $this->assertEquals($context->getStatusCode(), 404);
        $this->assertEquals($context->getMethod(), 'GET');
    }

    public function testArrayAccess()
    {
        $bundle = $this->app->getBundle();

        $this->app['foo'] = 'bar';

        $this->assertTrue(isset($this->app['foo']));

        $value = $this->app['foo'];
        $this->assertEquals($value, $bundle['foo']);

        unset($this->app['foo']);

        $this->assertNull($this->app['foo']);
        $this->assertNull($bundle['foo']);
    }

    public function testRun()
    {
        ob_start();
        $this->app->run(false);
        $result = ob_get_clean();
        $this->assertEquals($result, 'Not Found');

        ob_start();
        $app = new App();
        $app->run();
        ob_end_clean();
    }
}