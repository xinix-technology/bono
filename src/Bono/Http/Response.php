<?php

namespace Bono\Http;

class Response extends \Slim\Http\Response {

    protected $template = '';
    protected $data = array();

    public function set($key, $value = NULL) {
        if (is_array($key)) {
            $this->data = $key;
        } elseif (is_null($value)) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $value;
        }
    }

    public function get($key) {
        return $this->data[$key] ?: null;
    }

    public function template($template = null) {
        if (is_null($template)) {
            return $this->template;
        } else {
            $this->template = $template;
        }
    }

    public function data() {
        return $this->data;
    }

    public function redirect ($url = ':self', $status = 302) {
        if ($url === ':self') {
            $app = \Slim\Slim::getInstance();
            $url = $app->request->getResourceUri();
        }
        $url = \Bono\Helper\URL::site($url);
        return parent::redirect($url, $status);
    }

}