<?php

namespace Bono\Test;

use PHPUnit_Framework_TestCase;
use Bono\App;

class BonoTestCase extends PHPUnit_Framework_TestCase {
    protected $app;

    public function setUp()
    {
        $this->app = new App();
    }
}