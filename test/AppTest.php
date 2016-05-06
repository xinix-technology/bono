<?php
namespace Bono\Test;

use Bono\App;
use Bono\ErrorHandler;

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

    public function testRun()
    {
        $GLOBALS['test-coverage'] = true;

        ob_start();
        $this->app->run(false);
        $result = ob_get_clean();
        $this->assertEquals($result, 'Not Found');

        ob_start();
        $app = new App();
        $app->run();
        ob_end_clean();
    }

    public function testErrorHandler()
    {
        $app = new App();
        $this->assertEquals($app->getErrorHandler(), null);

        $app = new App(['cli' => false ]);
        $this->assertInstanceOf(ErrorHandler::class, $app->getErrorHandler());

        $app = new App(['cli' => false, 'error.handler' => Foo::class ]);
        $this->assertInstanceOf(Foo::class, $app->getErrorHandler());
    }

    public function testAddAndGetLogger()
    {
        $app = new App();
        $this->assertInstanceOf(\Monolog\Logger::class, $app->getLogger());

        $app = new App();
        $logger = new \Monolog\Logger('foo');
        $app->addLogger('foo', $logger);
        $this->assertEquals($app->getLogger('foo'), $app->getLogger());

        $app = new App([
            'loggers' => [
                'foo' => [ \Monolog\Logger::class, ['name' => 'bono'] ],
                'bar' => new \Monolog\Logger('bono'),
            ]
        ]);
        $this->assertEquals($app->getLogger(), $app->getLogger('foo'));
        $this->assertInstanceOf(\Monolog\Logger::class, $app->getLogger('bar'));
    }
}

class Foo {}