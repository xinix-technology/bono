<?php

namespace Bono\Handler;

use Whoops\Run;

class NotFoundHandler
{
    public function handle()
    {
        $app = \App::getInstance();

        if ($app->config('bono.debug') !== false) {
            $view    = new \Slim\View();

            $errorTemplate = '../templates/notFound.php';

            if (is_readable($errorTemplate)) {
                $view->setTemplatesDirectory('.');
                return $view->display($errorTemplate, array(), 404);
            }
        }

        $app->whoops->sendHttpCode(404);
        return call_user_func(array($app->whoops, Run::EXCEPTION_HANDLER), new \RuntimeException("404 Resource not found"));
    }
}
