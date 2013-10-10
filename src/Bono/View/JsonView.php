<?php

namespace Bono\View;

class JsonView extends \Slim\View {
    public $app;
    public $contentType = 'application/json';

    public function display($template) {
        $data = $this->data->all();
        unset($data['flash']);
        $this->app->response->headers['Content-Type'] = $this->contentType;

        echo \JsonKit\JsonKit::encode($data);
    }
}