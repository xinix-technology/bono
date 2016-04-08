<?php

namespace Bono;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;
use Exception;

class ErrorHandler extends WhoopsRun
{
    public function __construct($app)
    {
        $this->app = $app;

        $handler = new PrettyPageHandler();

        $handler->addResourcePath(__DIR__.'/../../templates/vendor/whoops');
        $handler->addResourcePath('../templates/vendor/whoops');

        $this->pushHandler($handler);
        $this->pushHandler(function () use ($handler) {
            $obs = [];
            while (ob_get_level() > 0) {
                $ob = trim(ob_get_contents());
                if ($ob) {
                    $obs[] = $ob;
                }
                ob_end_clean();
            }
            $handler->addDataTable('Output Buffers', $obs);

            // restart output buffer for error show
            ob_start();
        });
    }
}
