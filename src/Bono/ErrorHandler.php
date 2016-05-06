<?php

namespace Bono;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;
use Exception;
use Bono\App;

class ErrorHandler extends WhoopsRun
{
    protected $app;

    protected $handler;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->handler = new PrettyPageHandler();

        $this->handler->addResourcePath(__DIR__.'/../../templates/vendor/whoops');
        if (is_readable('../templates/vendor/whoops')) {
            $this->handler->addResourcePath('../templates/vendor/whoops');
        }

        $this->pushHandler($this->handler);
        $this->pushHandler([ $this, 'obHandler' ]);

        $this->register();
    }

    public function obHandler()
    {
        $obs = [];
        $levels = ob_get_level();
        while (ob_get_level() > 0) {
            $ob = trim(ob_get_contents());
            if ($ob) {
                $obs[] = $ob;
            }
            ob_end_clean();
        }
        $this->handler->addDataTable('Output Buffers', $obs);

        // restart output buffer for error show
        for($i = 0; $i < $levels; $i++) {
            ob_start();
        }
    }
}
