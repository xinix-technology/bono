<?php

namespace Bono\View;

use \Bono\App;

class JsonView extends \Slim\View {
    public $contentType = 'application/json';

    public function display($template) {
        $app = App::getInstance();
        $data = $this->data->all();
        unset($data['flash']);
        foreach ($data as $key => $value) {
            if ($key[0] === '_') {
                unset($data[$key]);
            }
        }

        $app->response->headers['content-type'] = $this->contentType;
        echo \JsonKit\JsonKit::encode($data);
    }
}
