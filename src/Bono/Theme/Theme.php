<?php

namespace Bono\Theme;

use Bono\App;

abstract class Theme {

    protected $extension = '.php';

    protected $templateDirectories = array();

    public function __construct($config = array()) {
        foreach ($config as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }

        $this->templateDirectories[] = App::getInstance()->config('bono.templates.path');
    }

    public function resolve($template, $view = null) {
        $segments = explode('/', $template);
        $page = end($segments);

        foreach ($this->templateDirectories as $dir) {
            if ($t = $this->tryWith($dir, $template, $view)) {
                return $t;
            }

            if ($t = $this->tryWith($dir, $template.$this->extension, $view)) {
                return $t;
            }
        }
    }

    public function tryWith($dir, $template, $view) {
        if (is_readable($f = ltrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$template)) {
            if (isset($view)) {
                $view->setTemplatesDirectory($dir);
            }
            return $template;
        }
    }

}