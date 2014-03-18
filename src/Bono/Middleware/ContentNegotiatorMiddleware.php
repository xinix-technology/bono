<?php

namespace Bono\Middleware;

class ContentNegotiatorMiddleware extends \Slim\Middleware {
    public function call() {
        if (empty($this->options)) {
            $this->options = $this->app->config('bono.contentNegotiator');
        }

        if ($this->options['extensions']) {
            $this->app->request->addMediaTypeExtensions($this->options['extensions']);
        }

        $mediaType = $this->app->request->getMediaType();
        $ext = $this->app->request->getExtension();

        try {
            $this->next->call();
        } catch (\Bono\Exception\RestException $e) {
            if (!isset($this->options['views'][$mediaType])) {
                throw $e;
            }
            $this->app->response->status($e->getCode());
            $this->app->response->set('errors', $e.'');
        }

        if (isset($this->options['views'][$mediaType])) {

            $include = $this->app->request->get('!include');
            if (!empty($include)) {
                \Norm\Norm::options('include', true);
            }

            $this->app->response->setBody('');
            $this->app->view($this->options['views'][$mediaType]);


            $status = $this->app->response->getStatus();
            // if ($status >= 200 && $status < 300) {
                $this->app->response->headers['content-type'] = $mediaType;
                $this->app->render($this->app->response->template(), $this->app->response->data());
                $this->app->stop();
            // } else

        }

    }
}
