<?php

namespace Bono\View;

class JsonView extends \Slim\View {
    public $app;
    public $contentType = 'application/json';

    public function display($template) {
        $data = $this->data->all();
        unset($data['flash']);
        foreach ($data as $key => $value) {
            if ($key[0] === '_') {
                unset($data[$key]);
            }
        }

        $this->app->response->headers['Content-Type'] = $this->contentType;
        echo \JsonKit\JsonKit::encode($data);
    }
}