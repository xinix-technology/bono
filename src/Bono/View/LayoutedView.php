<?php

namespace Bono\View;

use \Slim\View;

class LayoutedView extends View {
    public $app;

    protected $layout = 'layout';

    function __construct() {
        parent::__construct();

        $this->layoutView = new View();
    }

    public function fetch($template) {
        $html = parent::fetch($template.'.php');

        $data = array(
            'content' => $html,
            'app' => $this->app,
            );
        if (is_array($this->data)) {
            $data = $this->data + $data;
        }
        $this->layoutView->setTemplatesDirectory($this->templatesDirectory);
        $this->layoutView->appendData($data);
        return $this->layoutView->render($this->layout.'.php');
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }
}