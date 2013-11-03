<?php

namespace Bono\Http;

class Response extends \Slim\Http\Response {

    protected $template = '';
    protected $data;

    public function set($key, $value) {
        if (is_null($value)) {
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

}