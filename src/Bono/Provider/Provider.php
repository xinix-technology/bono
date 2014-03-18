<?php

namespace Bono\Provider;

abstract class Provider {

    protected $app;

    protected $options;

    public function __construct($options = null) {
        $this->options = $options;
    }

    public function setApplication($app) {
        $this->app = $app;
    }

    abstract public function initialize();
}