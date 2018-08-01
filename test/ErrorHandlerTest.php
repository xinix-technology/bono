<?php

namespace Bono\Test;

use PHPUnit\Framework\TestCase;
use Bono\ErrorHandler;
use ROH\Util\File;

class ErrorHandlerTest extends TestCase
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
        $errorHandler = new ErrorHandler();
        $this->assertTrue(true);
    }

    public function testHandleException()
    {
        $errorHandler = new ErrorHandler();
        echo 'fill the ob';
        $errorHandler->handleException(new \Exception('Unfortunate event arise'));
        $this->assertTrue(true);
    }
}
