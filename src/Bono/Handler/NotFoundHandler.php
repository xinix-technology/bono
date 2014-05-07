<?php

namespace Bono\Handler;

class NotFoundHandler
{

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function handle()
    {
        $app = $this->app;

        $view    = new \Slim\View();

        $errorTemplate = '../templates/notFound.php';

        if (is_readable($errorTemplate)) {
            $view->setTemplatesDirectory('.');
            $view->display($errorTemplate, array(), 404);
        } else {
            $app->response->setStatus(404);
            echo '<!doctype html>
<html>
<head>
    <title>Ugly Not Found!</title>
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
    <h1>Ugly Not Found!</h1>

    <p>Whoops! Apparently this is not the page you are looking for.</p>
    <blockquote>Edit this page by creating templates/notFound.php</blockquote>
</body>
</html>';
        }
    }
}
