<?php namespace Bono\Handler;

use Whoops\Run;
use Bono\App;
use Slim\View;
use RuntimeException;

class NotFoundHandler
{
    public function handle()
    {
        $app = App::getInstance();

        if ($app->config('bono.debug') !== true) {
            if (isset($app->response)) {
                $app->response->setStatus(404);
            }

            if (isset($app->theme)) {
                $view = $app->theme->getView();
                $errorTemplate = 'notFound';
            } else {
                $view = new View();
                $errorTemplate = '../templates/notFound.php';

                if (is_readable($errorTemplate)) {
                    $view->setTemplatesDirectory('.');
                }
            }

            return $view->display($errorTemplate, array(), 404);
        }

        $app->whoops->sendHttpCode(404);

        return call_user_func(array($app->whoops, Run::EXCEPTION_HANDLER), new RuntimeException("404 Resource not found"));
    }
}
