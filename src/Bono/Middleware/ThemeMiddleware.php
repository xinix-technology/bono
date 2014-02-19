<?php

namespace Bono\Middleware;

/**
 * deprecated
 */
class ThemeMiddleware extends \Slim\Middleware {
    public function call() {
        $app = $this->app;

        $config = $app->config('bono.theme');
        $Theme = $config['class'];

        if (isset($Theme)) {
            $app->theme = new $Theme($app, $config);
        }

        $this->next->call();

        $response = $app->response;
        $template = $response->template();

        if ($response->getStatus() == 200) {
            $app->render($template, $response->data());
        }
    }
}