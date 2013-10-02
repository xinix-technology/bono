<?php

namespace Bono\Provider;

class ContentNegotiatorProvider {
    protected $app;

    public function initialize($app) {
        $this->app = $app;

        $app->container->singleton('request', function ($c) {
            return new \Bono\Http\Request($c['environment']);
        });

        $config = $app->config('bono.view');

        if ($config['layout']) {
            $app->layoutTemplate = $config['layout'];
        }

        $app->hook('slim.after.router', function() use ($app, $config) {
            $mime = $app->request->getMime($app->config('bono.forceMimeType'));

            $data = $app->data ?: array();

            if ($mime[0] == 'json') {

                $app->view('\\Bono\\View\\JsonView');
                $app->view->app = $app;
                $app->view->contentType = $mime[1];

                $app->render('', $data, $app->status);

            } else {

                $app->view($config['default'] ? $config['default'] : '\\Bono\\View\\LayoutedView');
                $app->view->app = $app;

                if (isset($app->layoutTemplate)) {
                    $app->view->setLayout($app->layoutTemplate);
                }

                if (!$app->viewTemplate) {
                    // $app->viewTemplate = 'blank';
                    $data['content'] = ob_get_clean();
                }
                $app->render($app->viewTemplate, $data);
            }
        });
    }
}