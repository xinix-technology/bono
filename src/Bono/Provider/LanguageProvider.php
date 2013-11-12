<?php

namespace Bono\Provider;

class LanguageProvider extends Provider {

    public function initialize() {
        $this->app->view->set('_', function($str) {
            return $str;
        });
    }

}