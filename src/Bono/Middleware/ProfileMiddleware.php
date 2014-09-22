<?php

namespace Bono\Middleware;

use Slim\Middleware;

class ProfileMiddleware extends Middleware
{
    protected $mark;

    protected $data = array(
        'system' => array()
    );

    public function call()
    {
        $app = $this->app;

        $debug = $app->config('bono.debug');

        $that = $this;

        if ($debug) {
            $app->filter('profile.add', function ($opts) use ($that) {
                $that->add($opts);
            });

            $app->filter('profile.display', function () use ($that) {
                return $that->display();
            });
        }

        $this->mark = microtime(true);

        $this->next->call();
    }

    public function data()
    {
        $this->data['system'] = array(
            'memory' => array(
                'value' => memory_get_usage()
            ),
            'time' => array(
                'value' => microtime(true) - $this->mark
            ),
        );

        return $this->data;
    }

    protected function add($opts)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $data = array(
            'value' => $opts['value'],
            'trace' => array_slice($trace, 5),
        );

        if (is_null($opts['section'])) {
            $opts['section'] = 'global';
        }
        if (!isset($this->data[$opts['section']])) {
            $this->data[$opts['section']] = array();
        }

        if (isset($opts['key'])) {
            $this->data[$opts['section']][$opts['key']] = $data;
        } else {
            $this->data[$opts['section']][] = $data;
        }
    }

    public function display()
    {
        $app = \App::getInstance();

        return $app->theme->partial('profile_middleware/display', array(
            '_profile' => $this->data(),
        ));
    }
}
