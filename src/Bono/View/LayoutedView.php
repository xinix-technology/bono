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

            $data = array(
                'body' => $html,
                'app' => $this->app,
            );

            if (is_array($this->data)) {
                $data = array_merge($data, $this->data);
            }
            $this->layoutView->setTemplatesDirectory($this->templatesDirectory);
            $this->layoutView->appendData($data);
            return $this->layoutView->render($this->layout.'.php');
        }
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }
}