<?php

namespace Bono\Provider;

class ContentNegotiatorProvider {
    protected $app;

    public function initialize($app) {
        $this->app = $app;

        $app->container->singleton('request', function ($c) {
            return new \Bono\Http\Request($c['environment']);
        });

        $app->hook('slim.after.router', function() use ($app) {
            $mime = $app->request->getMime();
            if ($mime[0] == 'json') {
                $app->response->headers['Content-Type'] = $mime[1];
                echo json_encode($app->data, $app->config('debug') ? JSON_PRETTY_PRINT : 0);
            } elseif ($app->viewTemplate) {
                if (!is_array($app->data)) {
                   $app->data = array();
                }
                $app->render($app->viewTemplate.'.php', $app->data);
            }
        });
    }
}