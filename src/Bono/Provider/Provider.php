<?php

namespace Bono\Provider;

abstract class Provider {
    protected $app;

    public function setApp($app) {
        $this->app = $app;
    }

    abstract public function initialize();
}