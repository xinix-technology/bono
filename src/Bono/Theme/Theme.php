<?php

namespace Bono\Theme;

abstract class Theme {
    protected $app;

    protected $baseDir;

    public function __construct($app, $config) {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        $this->app = $app;
    }

    public function getTemplate($template, $ext = '.php') {
        $page = explode('/', $template);
        $page = end($page);

        if (is_readable($this->getPath('templates/'.$template.$ext))) {
            return $template;
        }

        $p = 'shared/'.$page;
        if (is_readable($this->getPath('templates/'.$p.$ext))) {
            return $p;
        }

        return false;
    }

    public function getPath($p = 'templates') {
        return $this->baseDir.$p;
    }

    public function fetchWith($view, $template) {
        if ($t = $this->getTemplate($template, '')) {
            $view->setTemplatesDirectory($this->getPath());
            $template = $t;
        }

        return $view->fetch($template);
    }
}