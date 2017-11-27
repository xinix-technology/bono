<?php

namespace Bono\Test;

use PHPUnit\Framework\TestCase;
use Bono\App;

class BonoTestCase extends TestCase {
    protected $app;

    public function setUp()
    {
        $this->app = new App();
    }
}