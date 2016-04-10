<?php

namespace Bono\Test;

use Bono\ErrorHandler;

class ErrorHandlerTest extends BonoTestCase
{
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
        mkdir('../templates/vendor/whoops', 0755, true);
    }

    public function tearDown()
    {
        $this->deleteDir('../templates/vendor/whoops');
    }

    public function testConstruct()
    {
        $errorHandler = new ErrorHandler($this->app);
    }

    public function testHandleException()
    {
        $errorHandler = new ErrorHandler($this->app);
        echo 'fill the ob';
        $errorHandler->handleException(new \Exception('Unfortunate event arise'));
    }
}