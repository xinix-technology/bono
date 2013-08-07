<?php

namespace Bono;

use Doctrine\Common\Inflector\Inflector;

class Controller {

    protected $app;

    protected $clazz;

    protected $name;

    public function __construct($app) {
        $this->app = $app;

        if (is_null($this->clazz)) {
            $class = explode('\\', get_class($this));
            $this->clazz = end($class);
        }
        $this->name = Inflector::tableize($this->clazz);
    }

    public function register() {

    }

}