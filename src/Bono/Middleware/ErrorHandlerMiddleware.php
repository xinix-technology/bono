<?php

namespace Bono\Middleware;

class ErrorHandlerMiddleware extends \Slim\Middleware {
    public function call() {
        try {
            $this->next->call();
        } catch(\Exception $e) {
            try {
                $this->app->error($e);
            } catch (\Slim\Exception\Stop $e) {
                // Do nothing
            }
        }
    }
}