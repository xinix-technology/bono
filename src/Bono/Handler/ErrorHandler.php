<?php

namespace Bono\Handler;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class ErrorHandler
{
    protected $app;
    protected $run;

    public function __construct($app)
    {
        $this->app = $app;

        if ($app->config('bono.debug') !== false) {
            $this->run = new Run;

            $handler = new PrettyPageHandler;
            $path = explode('/src/', __DIR__);
            $path = $path[0].'/templates/_whoops';
            $handler->setResourcesPath($path);

            $jsonResponseHandler = new JsonResponseHandler;
            $jsonResponseHandler->onlyForAjaxRequests(true);

            $appHandler = function () use ($app, $handler) {
                if (!isset($app->request)) {
                    return;
                }

                $request = $app->request;

                // Add some custom tables with relevant info about your application,
                // that could prove useful in the error page:
                $handler->addDataTable('Bono Application', array(
                    'Charset'          => $request->headers('ACCEPT_CHARSET'),
                    'Locale'           => $request->getContentCharset() ?: '<none>',
                    'Application Class'=> get_class($app)
                ));

                $handler->addDataTable('Bono Request', array(
                    'URI'         => $request->getRootUri(),
                    'Request URI' => $request->getResourceUri(),
                    'Path'        => $request->getPath(),
                    'Query String'=> $request->params() ?: '<none>',
                    'HTTP Method' => $request->getMethod(),
                    'Script Name' => $request->getScriptName(),
                    'Base URL'    => $request->getUrl(),
                    'Scheme'      => $request->getScheme(),
                    'Port'        => $request->getPort(),
                    'Host'        => $request->getHost(),
                ));

                // Set the title of the error page:
                $handler->setPageTitle("Whoops! There was a problem.");
            };

            $this->run->pushHandler($handler);

            // Add a special handler to deal with AJAX requests with an
            // equally-informative JSON response. Since this handler is
            // first in the stack, it will be executed before the error
            // page handler, and will have a chance to decide if anything
            // needs to be done.
            $this->run->pushHandler($jsonResponseHandler);
            $this->run->pushHandler($appHandler);
        }
    }

    public function handle($e)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        if (isset($this->run)) {
            return call_user_func_array(array($this->run, Run::EXCEPTION_HANDLER), func_get_args());
        } else {
            if (isset($this->app->response)) {
                $this->app->response->setStatus(500);
            }

            $view = new \Slim\View();
            $errorTemplate = '../templates/error.php';

            if (is_readable($errorTemplate)) {
                $view->setTemplatesDirectory('.');
                $view->display($errorTemplate, array(), 500);
            } else {
                echo '<!doctype html>
<html>
<head>
    <title>Ugly Error!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        body { font-family: Arial; font-size: 14px; line-height: 1.5; color: #333 }
        h1 { border-bottom: 1px solid #88f; font-weight: normal; }
        label { margin-top: 10px; display: block; font-size: .8em; font-weight: bold; }
        pre { margin: 0}
        blockquote { font-size: .8em; font-style: italic; margin: 0; }
        .row, .stack-trace { border: 1px solid #f88; padding: 5px; border-radius: 5px;
            background-color: #fee; overflow: auto; }
    </style>
</head>
<body>
    <h1>Ugly Error!</h1>

    <p>Whoops! Something bad happened.</p>

    <p>
    <label>Code</label>'.$e->getCode().'<br>
    <label>Message</label>'.$e->getMessage().'<br>
    <label>At</label>'.$e->getFile().':'.$e->getLine().'
    </p>

    <p>
    <pre>'.$e->getTraceAsString().'</pre>
    </p>

    <blockquote>Edit this page by creating templates/error.php</blockquote>
</body>
</html>';
            }
        }
    }
}
