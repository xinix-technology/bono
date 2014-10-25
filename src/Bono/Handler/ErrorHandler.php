<?php

namespace Bono\Handler;

use Whoops\Run;
use Bono\App;

class ErrorHandler
{
    public function handle($e)
    {
        $app = App::getInstance();

        while (ob_get_level() > 0) ob_end_clean();

        // header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        if ($app->config('bono.debug') !== false) {
            if (isset($this->app->response)) {
                $this->app->response->setStatus(500);
            }

            $view = new \Slim\View();
            $errorTemplate = '../templates/error.php';

            if (is_readable($errorTemplate)) {
                $view->setTemplatesDirectory('.');
                return $view->display($errorTemplate, array(), 500);
            }
        }

        return call_user_func_array(array($app->whoops, Run::EXCEPTION_HANDLER), func_get_args());
    }
}
