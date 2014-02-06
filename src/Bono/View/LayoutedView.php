<?php

namespace Bono\View;

class LayoutedView extends \Slim\View {
    protected $layout = 'layout';

    protected $layoutView;

    protected $app;

    function __construct() {
        parent::__construct();

        $this->app = \Bono\App::getInstance();
        $this->layoutView = new \Slim\View();
        $this->layoutView->setTemplatesDirectory($this->templatesDirectory);
    }

    public function fetch($template) {
        $theme = $this->app->theme;
        if (empty($template)) {
            return $this->data['body'];
        } else {
            if ($theme && $t = $theme->getTemplate($template)) {
                $this->setTemplatesDirectory($theme->getPath());
                $template = $t;
            }
            $html = parent::fetch($template.'.php');

            if ($this->layout) {
                if ($theme && $t = $theme->getTemplate($this->layout)) {
                    $this->layoutView->setTemplatesDirectory($theme->getPath());
                    $template = $t;
                } else {
                    $template = $this->layout;
                }
                $this->layoutView->replace($this->all());
                $this->layoutView->set('body', $html);
                return $this->layoutView->render($template.'.php');
            } else {
                return $html;
            }
        }
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }
}