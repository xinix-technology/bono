<?php

namespace Bono;

use Reekoheek\Util\Inflector;

class Controller {

    protected $app;

    protected $clazz;

    protected $name;

    public function __construct($app, $name = NULL) {
        $this->app = $app;

        if (isset($name)) {
            $this->name = $name;
            $this->clazz = Inflector::classify($this->name);
        } else {
            $exploded = explode('\\', get_class($this));
            $this->clazz = end($exploded);
            $this->name = Inflector::tableize($this->clazz);
        }

    }
}