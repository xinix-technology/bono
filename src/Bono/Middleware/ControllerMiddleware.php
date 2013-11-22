<?php

namespace Bono\Middleware;

class ControllerMiddleware extends \Slim\Middleware {
    public function call() {
        $config = $this->app->config('bono.controllers');
        $mapping = $config['mapping'];

        $resourceUri = $this->app->request->getResourceUri();

        foreach ($mapping as $uri => $Map) {
            if (strpos($resourceUri, $uri) === 0) {
                if (is_null($Map)) {
                    $Map = $config['default'];
                }
                $this->app->controller = $controller = new $Map($this->app, $uri);
                if (!$controller instanceof \Bono\Controller\Controller) {
                    throw new \Exception('Controller "'.$Map.'" should be instance of \Bono\Controller\Controller.');
                }
                break;
            }
        }

        $this->next->call();
    }
}