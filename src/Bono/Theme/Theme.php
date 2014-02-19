<?php

namespace Bono\Theme;

use Bono\App;

abstract class Theme {

    protected $extension = '.php';

    protected $baseDirectories = array();

    public function __construct($config = array()) {
        foreach ($config as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }


        if ($p = realpath(rtrim(App::getInstance()->config('bono.base.path'), DIRECTORY_SEPARATOR))) {
            $this->baseDirectories[] = $p;
        }
    }

    public function resolve($template, $view = null) {
        $segments = explode('/', $template);
        $page = end($segments);


        foreach ($this->baseDirectories as $dir) {
            if ($t = $this->tryTemplate($dir, $template, $view)) {
                return $t;
            }

            if ($t = $this->tryTemplate($dir, $template.$this->extension, $view)) {
                return $t;
            }

            if ($t = $this->tryTemplate($dir, 'shared'.DIRECTORY_SEPARATOR.$page, $view)) {
                return $t;
            }

            if ($t = $this->tryTemplate($dir, 'shared'.DIRECTORY_SEPARATOR.$page.$this->extension, $view)) {
                return $t;
            }
        }
    }

    public function tryTemplate($dir, $template, $view) {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'templates';

        if (is_readable($f = $dir.DIRECTORY_SEPARATOR.$template)) {
            if (isset($view)) {
                $view->setTemplatesDirectory($dir);
            }
            return $template;
        }
    }


    public function partial($template, $data) {
        $app = App::getInstance();
        $Clazz = $app->config('bono.partial.view');

        $view = new $Clazz;
        $template = $this->resolve($template, $view);
        $view->replace($data);
        return $view->fetch($template);
    }
}