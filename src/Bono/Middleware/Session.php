<?php

namespace Bono\Middleware;

use Bono\App;
use Bono\Http\Context;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Exception;

class Session
{
    protected $app;

    protected $options;

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;

        $this->options = (new Options([
            'name' => 'BSESS',
            'lifetime' => '2 years',
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httpOnly' => false,
            'autorefresh' => false,
        ]))->merge($options)->toArray();

        if (is_string($this->options['lifetime'])) {
            $this->options['lifetime'] = strtotime($this->options['lifetime']) - time();
        }

        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1);
        ini_set('session.gc_maxlifetime', 24 * 30 * 24 * 60 * 60);
    }

    public function __invoke(Context $context, $next)
    {
        if ($this->app->isCli()) {
            return $next($context);
        } else {
            $this->start($context);
            try {
                $next($context);
            } catch (Exception $e) {
                $lastError = $e;
            }

            $this->stop($context);

            if (isset($lastError)) {
                throw $lastError;
            }
        }
    }

    protected function start(Context $context, $keep = false)
    {
        // it sure that it wont be called more than once since it is protected not public
        // if (session_id()) {
        //     return;
        // }
        $context['@session'] = $this;

        $options = $this->options;
        $options['path'] = rtrim(rtrim($context['original.uri']->getBasepath(), '/index.php'), '/') . '/';

        if (!$keep) {
            $options['lifetime'] = isset($_COOKIE['keep']) ? (int) $_COOKIE['keep'] : 0;
        }

        $context['@session.options'] = $options;

        session_set_cookie_params(
            $options['lifetime'],
            $options['path'],
            $options['domain'],
            $options['secure'],
            $options['httpOnly']
        );
        session_name($options['name']);
        session_cache_limiter(false);
        @session_start();

        if (ini_get('session.use_cookies') && $options['lifetime'] > 0) {
            @setcookie(session_name(), session_id(), time() + $options['lifetime'], $options['path']);
            @setcookie('keep', $options['lifetime'], time() + $options['lifetime'], $options['path']);
            $_COOKIE['keep'] = $options['lifetime'];
        }

        $context['@session.data'] = new UtilCollection($_SESSION);
    }

    protected function stop(Context $context)
    {
        foreach ($context['@session.data'] as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    protected function destroy(Context $context)
    {
        $options = $context['@session.options'];

        unset($_SESSION);
        session_unset();
        session_destroy();
        session_write_close();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            @setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            unset($_COOKIE['keep']);
            @setcookie('keep', '', time() - 3600, $options['path']);
        }


        unset($context['@session.options']);
    }

    public function reset(Context $context, $keep = false)
    {
        $this->destroy($context);
        $this->start($context, $keep);
        @session_regenerate_id(true);
    }

    public function get(Context $context, $key, $default = null)
    {
        if (isset($context['@session.data'][$key])) {
            return $context['@session.data'][$key];
        } else {
            return $default;
        }
    }

    public function set(Context $context, $key, $value)
    {
        $context['@session.data'][$key] = $value;
    }
}