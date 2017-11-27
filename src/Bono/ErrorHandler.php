<?php

namespace Bono;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;
use Exception;
use Bono\App;

class ErrorHandler
{
    protected $app;

    protected $runner;

    protected $handler;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->runner = new WhoopsRun();
        $this->runner->allowQuit(false);

        $this->handler = new PrettyPageHandler();

        $this->handler->addResourcePath(__DIR__.'/../../templates/vendor/whoops');
        if (is_readable('../templates/vendor/whoops')) {
            $this->handler->addResourcePath('../templates/vendor/whoops');
        }

        $this->runner->pushHandler($this->handler);
        $this->runner->pushHandler([ $this, 'obHandler' ]);
    }

    public function register()
    {
        $this->runner->register();
    }

    public function getHandler()
    {
        return $this->handler;
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
        for ($i = 0; $i < $levels; $i++) {
            ob_start();
        }
    }

    public function handleException(Exception $exception, $returnResult = false)
    {
        if ($returnResult) {
            $writeToOutput = $this->writeToOutput();
            $this->writeToOutput(false);
            $result = $this->runner->handleException($exception);
            $this->writeToOutput($writeToOutput);
            return $result;
        } else {
            $this->runner->handleException($exception);
        }
    }
}
