<?php

namespace Bono\View;

class LayoutedView extends \Slim\View {
    public $app;

    protected $layout = 'layout';

    protected $layoutView;

    function __construct() {
        parent::__construct();

        $this->layoutView = new \Slim\View();
    }

    public function fetch($template) {
        if (empty($template)) {
            return $this->data['body'];
        } else {
            $html = parent::fetch($template.'.php');

            if ($this->layout) {
                $this->layoutView->setTemplatesDirectory($this->templatesDirectory);
                $this->layoutView->replace($this->all());
                $this->layoutView->set('body', $html);
                return $this->layoutView->render($this->layout.'.php');
            } else {
                return $html;
            }
        }
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }
}