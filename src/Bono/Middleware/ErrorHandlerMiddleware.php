<?php

namespace Bono\Middleware;

class ErrorHandlerMiddleware extends \Slim\Middleware {
    public function call() {
        try {
            ob_start();
            $this->next->call();
        } catch (\Slim\Exception\Stop $e) {
            // $body = ob_get_clean();
            // $this->app->response()->write($body);
            $this->app->applyHook('slim.after');
        } catch(\Exception $e) {
            if (ob_get_level() !== 0) {
                ob_clean();
            }
            try {
                $this->app->error($e);
            } catch (\Slim\Exception\Stop $e) {
                // Do nothing
            }
        }
    }
}
