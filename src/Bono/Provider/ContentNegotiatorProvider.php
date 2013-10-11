<?php

namespace Bono\Provider;

class ContentNegotiatorProvider implements Provider {
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

            if ($mime[0] == 'json') {

                $app->view('\\Bono\\View\\JsonView');
                $app->view->app = $app;
                $app->view->contentType = $mime[1];

                $app->render('', $app->published, $app->status);

            } else {

                $app->view($config['default'] ? $config['default'] : '\\Bono\\View\\LayoutedView');
                $app->view->app = $app;

                if (isset($app->layoutTemplate)) {
                    $app->view->setLayout($app->layoutTemplate);
                }

                $data = array(
                    'published' => $app->published,
                    'helper' => $app->helper
                );

                if (!$app->viewTemplate) {
                    // $app->viewTemplate = 'blank';
                    $data['content'] = ob_get_clean();
                }
                $app->render($app->viewTemplate, $data);
            }
        });
    }
}