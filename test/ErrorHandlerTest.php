<?php

namespace Bono\Test;

use Bono\ErrorHandler;
use ROH\Util\File;

class ErrorHandlerTest extends BonoTestCase
{
    public function setUp()
    {
        parent::setUp();
        mkdir('../templates/vendor/whoops', 0755, true);
    }

    public function tearDown()
    {
        File::rm('../templates/vendor/whoops');
        parent::tearDown();
    }

    public function testConstruct()
    {
        $errorHandler = new ErrorHandler($this->app);
        $this->assertTrue(true);
    }

    public function testHandleException()
    {
        $errorHandler = new ErrorHandler($this->app);
        echo 'fill the ob';
        $errorHandler->handleException(new \Exception('Unfortunate event arise'));
        $this->assertTrue(true);
    }
}
