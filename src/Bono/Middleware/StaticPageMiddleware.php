<?php

namespace Bono\Middleware;

class StaticPageMiddleware extends \Slim\Middleware
{
    public function call()
    {
        $app = $this->app;

        if ($app->request->isGet() && is_null($app->controller)) {
            $pathInfo = $app->request->getPathInfo();
            $template = 'static'.($pathInfo ?: '/index');

            if (!is_null($app->theme->resolve($template))) {
                $app->get($pathInfo ?: '/', function () use ($app, $template) {
                    $app->response->template($template);
                });
            }
        }

        $this->next->call();
    }
}
