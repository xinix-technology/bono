<?php

namespace Bono\View;

use Bono\App;

class LayoutedView extends \Slim\View {
    protected $layout = 'layout';

    protected $layoutView;

    function __construct() {
        parent::__construct();

        $this->layoutView = new \Slim\View();
    }

    public function fetch($template) {

        $app = App::getInstance();

        if (empty($template)) {
            return $this->data['body'];
        } else {
            if ($app->theme) {
                $template = $app->theme->resolve($template, $this);
            }

            $html = parent::fetch($template);

            if ($this->layout) {
                if ($app->theme) {
                    $template = $app->theme->resolve($this->layout, $this->layoutView);
                } else {
                    $template = $this->layout;
                }
                $this->layoutView->replace($this->all());
                $this->layoutView->set('body', $html);
                return $this->layoutView->render($template);
            } else {
                return $html;
            }
        }
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }
}
