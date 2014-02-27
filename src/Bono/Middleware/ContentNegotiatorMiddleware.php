<?php

namespace Bono\Middleware;

class ContentNegotiatorMiddleware extends \Slim\Middleware {
    public function call() {
        $config = $this->app->config('bono.contentNegotiator');

        if ($config) {
            if ($config['extensions']) {
                $this->app->request->addMediaTypeExtensions($config['extensions']);
            }
        }

        $this->next->call();

        $mediaType = $this->app->request->getMediaType();
        $ext = $this->app->request->getExtension();

        try {
            $this->next->call();
        } catch (\Bono\Exception\RestException $e) {
            if (!isset($config['views'][$mediaType])) {
                throw $e;
            }
            $this->app->response->status($e->getCode());
            $this->app->response->set('errors', $e.'');
        }


        if (isset($config['views'][$mediaType])) {
            $this->app->response->setBody('');
            $this->app->view($config['views'][$mediaType]);


            $status = $this->app->response->getStatus();
            // if ($status >= 200 && $status < 300) {
                $this->app->response->headers['content-type'] = $mediaType;
                $this->app->render($this->app->response->template(), $this->app->response->data());
                $this->app->stop();
            // } else

        }

    }
}
