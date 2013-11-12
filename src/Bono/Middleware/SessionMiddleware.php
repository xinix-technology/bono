<?php

namespace Bono\Middleware;

class SessionMiddleware extends \Slim\Middleware {

    public function call() {
        $this->app->session = $this;

        $this->start();

        $this->next->call();
    }

    public function start() {
        session_start();
    }

    public function stop() {
        session_destroy();
    }

    public function restart() {
        $this->stop();
        $this->start();
    }
}