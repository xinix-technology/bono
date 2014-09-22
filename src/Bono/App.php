<?php

/**
 * Bono - PHP5 Web Framework
 *
 * MIT LICENSE
 *
 * Copyright (c) 2014 PT Sagara Xinix Solusitama
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category  PHP_Framework
 * @package   Bono
 * @author    Ganesha <reekoheek@gmail.com>
 * @copyright 2014 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version   0.10.0
 * @link      http://xinix.co.id/products/bono
 */
namespace Bono;

use Slim\Slim;
use Bono\Provider\ProviderRepository;
use Bono\Exception\FatalException;
use Bono\Handler\ErrorHandler;
use Bono\Handler\NotFoundHandler;

use Whoops\Run;

/**
 * App
 * Bono default application context
 *
 * @category  PHP_Framework
 * @package   Bono
 * @author    Ganesha <reekoheek@gmail.com>
 * @copyright 2014 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version   0.10.0
 * @link      http://xinix.co.id/products/bono
 */
class App extends Slim
{
    /**
     * Application context state whether it is running or not
     * @var boolean
     */
    protected $isRunning = false;

    protected $filters = array();

    protected $aliases = array(
        'App' => '\\Bono\\App',
        'URL' => '\\Bono\\Helper\\URL',
        'Theme' => '\\Bono\\Theme\\Theme',
    );

    /**
     * Override default settings
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        $settings = parent::getDefaultSettings();

        $settings['templates.path'] = '';
        $settings['bono.base.path'] = '..';
        $settings['bono.theme'] = '\\Bono\\Theme\\DefaultTheme';
        $settings['config.path'] = '../config';
        $settings['debug'] = true;
        $settings['autorun'] = true;
        $settings['bono.cli'] = (PHP_SAPI === 'cli');
        $settings['bono.timezone'] = 'UTC';

        if (!isset($settings['bono.debug'])) {
            $settings['bono.debug'] = ($settings['mode'] == 'development') ? true : false;
        }

        $settings['view'] = '\\Bono\\View\\LayoutedView';
        $settings['bono.partial.view'] = '\\Slim\\View';

        return $settings;
    }

    /**
     * Constructor
     *
     * @param array $userSettings Override settings from parameter
     */
    public function __construct(array $userSettings = array())
    {

        // FIXME ob started by php automatically but not skip on error
    // thats why i put line below
        ob_start();

        register_shutdown_function(array($this, 'shutdownHandler'));
        set_error_handler(array($this, 'handleErrors'));

        try {
            date_default_timezone_set('UTC');

            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http') {
                    unset($_SERVER['HTTPS']);
                } else {
                    $_SERVER['HTTPS'] = 'on';
                }

                if (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
                    $_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_FORWARDED_PORT'];
                }
            }

            if (PHP_SAPI === 'cli') {
                \Bono\CLI\Environment::getInstance();
            }

            parent::__construct($userSettings);

            $this->container->singleton(
                'request',
                function ($c) {
                    return new \Bono\Http\Request($c['environment']);
                }
            );

            $this->container->singleton(
                'response',
                function ($c) {
                    return new \Bono\Http\Response();
                }
            );

            $this->container->singleton(
                'theme',
                function ($c) {
                    $config = $c['settings']['bono.theme'];
                    if (is_array($config)) {
                        $themeClass = $config['class'];
                    } else {
                        $themeClass = $config;
                        $config = array();
                    }

                    return ($themeClass instanceof \Bono\Theme\Theme) ? $themeClass : new $themeClass($config);
                }
            );

            $app = $this;

            $this->view = function ($c) use ($app) {
                return $app->theme->getView();
            };

            $this->configureHandler();

            $this->configure();

            $this->configureAliases();

            $this->configureProvider();

            $this->configureMiddleware();

            if ($this->config('autorun')) {
                $this->run();
            }
        } catch (\Slim\Exception\Stop $e) {
            // noop
        }

    }

    public function shutdownHandler()
    {
        $e = error_get_last();
        if ($e) {
            $this->configureHandler()->error(new FatalException($e));
        }
    }

    /**
     * Override callErrorHandler
     * @param  [type] $argument [description]
     * @return [type] [description]
     */
    protected function callErrorHandler($argument = null)
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        return parent::callErrorHandler($argument);
    }

    /**
     * Override error
     * @param [type] $argument [description]
     * @return
     */
    public function error($argument = null)
    {
        if (is_callable($argument)) {
            return parent::error($argument);
        } else {
            if (isset($this->container['response'])) {
                try {
                    return parent::error($argument);
                } catch (\Slim\Exception\Stop $e) {
                    exit(1);
                }
            } else {
                echo $this->callErrorHandler($argument);
                exit(1);
            }
        }
    }

    /**
     * Override run method
     *
     * @return void
     */
    public function run()
    {
        require_once dirname(__FILE__).'/../functions.php';

        if ($this->isRunning) {
            return;
        }

        $this->isRunning = true;

        $this->add(new \Bono\Middleware\CommonHandlerMiddleware());

        $app = $this;

        $this->filter('app', function () use ($app) {
            return $app;
        });

        $this->filter('config', function ($key) use ($app) {
            if ($key) {
                return $app->config($key);
            } else {
                return $app->settings;
            }
        });

        parent::run();
    }

    /**
     * Check whether application has middleware with class name
     *
     * @param string $Clazz Class name
     *
     * @return boolean
     */
    public function has($Clazz)
    {
        if ($Clazz[0] == '\\') {
            $Clazz = substr($Clazz, 1);
        }
        foreach ($this->middleware as $middleware) {
            if (get_class($middleware) === $Clazz) {
                return true;
            }
        }

        return false;
    }

    /**
     * Configure life cycle
     *
     * @return void
     */
    protected function configure()
    {
        if (is_readable($configFile = $this->config('config.path') . '/config.php')) {
            $config = include $configFile;
            if (!is_array($config)) {
                $config = (array) $config;
            }
            $this->config($config);
        }
        if (is_readable($configFile = $this->config('config.path') . '/config-' . $this->config('mode') . '.php')) {
            $config = include $configFile;
            if (!is_array($config)) {
                $config = (array) $config;
            }
            $this->config($config);
        }

        $timezone = $this->config('bono.timezone');
        if (isset($timezone)) {
            date_default_timezone_set($timezone);
        }
    }

    public function config($name, $value = null)
    {
        if (func_num_args() === 1) {
            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    $this->config($key, $value);
                }
            } else {
                return parent::config($name);
            }
        } else {
            $settings = $this->settings;
            if (is_array($value)) {
                if (empty($settings[$name]) || !is_array($settings[$name])) {
                    $settings[$name] = array();
                }
                if (! is_array($settings[$name])) {
                    $settings[$name] = (array) $settings[$name];
                }
                $settings[$name] = array_merge($settings[$name], $value);
            } else {
                $settings[$name] = $value;
            }
            $this->settings = $settings;
        }
    }

    /**
     * Configure the alias class name
     *
     * @return void
     */
    protected function configureAliases()
    {
        $this->aliases = array_merge($this->aliases, $this->config('bono.aliases') ?: array());

        foreach ($this->aliases as $key => $value) {
            if (! class_exists($key)) {
                class_alias($value, $key);
            }
        }
    }

    /**
     * Configure handler
     * Right now there are 2 handlers: onNotFound and onError
     *
     * @return void
     */
    protected function configureHandler()
    {
        if ($this->config('_handlerConfigured') !== true) {
            $app = $this;

            if ($this->config('bono.cli') !== true) {
                $this->notFound(array(new NotFoundHandler($this), 'handle'));
                $this->error(array(new ErrorHandler($this), 'handle'));
            }

            $this->config('_handlerConfigured', true);
        }

        return $this;
    }

    /**
     * Configure providers
     *
     * @return void
     */
    protected function configureProvider()
    {
        $this->providerRepository = new ProviderRepository($this);

        $providers = $this->config('bono.providers') ?: array();

        if ($this->config('bono.cli')) {
            $this->providerRepository->add(new \Bono\Provider\CLIProvider());
        }

        foreach ($providers as $k => $v) {

            $Provider = $v;
            $options = null;
            if (is_string($k)) {
                $Provider = $k;
                $options = $v;
            }

            $this->providerRepository->add(new $Provider($options));
        }

        $this->providerRepository->initialize();
    }

    /**
     * Configure middlewares
     *
     * @return void
     */
    protected function configureMiddleware()
    {
        $middlewares = $this->config('bono.middlewares') ?: array();
        foreach ($middlewares as $k => $v) {
            $Middleware = $v;
            $options = null;
            if (is_string($k)) {
                $Middleware = $k;
                $options = $v;
            }
            $m = new $Middleware();
            $m->options = $options;
            $this->add($m);
        }
    }

    /********************************************************************************
    * Hooks
    *******************************************************************************/

    public function hook($name, $callable, $priority = 10, $override = false)
    {
        if ($override) {
            $this->clearHooks($name);
        }

        return parent::hook($name, $callable, $priority);
    }

    /********************************************************************************
    * Filters
    *******************************************************************************/

    /**
     * Assign filter
     *
     * @param string $name     The filter name
     * @param mixed  $callable A callable object
     * @param int    $priority The filter priority; 0 = high, 10 = low
     *
     * @return void
     */
    public function filter($name, $callable, $priority = 10, $override = false)
    {
        if ($override) {
            $this->clearFilters($name);
        }

        if (!isset($this->filters[$name])) {
            $this->filters[$name] = array(array());
        }
        if (is_callable($callable)) {
            $this->filters[$name][(int) $priority][] = $callable;
        }
    }

    /**
     * Invoke filter
     *
     * @param string $name      The filter name
     * @param mixed  $filterArg (Optional) Argument for filtered functions
     *
     * @return void
     */
    public function applyFilter($name, $filterArg = null)
    {
        if (!isset($this->filters[$name])) {
            $this->filters[$name] = array(array());
        }
        if (!empty($this->filters[$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count($this->filters[$name]) > 1) {
                ksort($this->filters[$name]);
            }
            foreach ($this->filters[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        $filterArg = call_user_func($callable, $filterArg);
                    }
                }
            }
        }

        return $filterArg;
    }

    /**
     * Get filter listeners
     *
     * Return an array of registered filters. If `$name` is a valid
     * filter name, only the listeners attached to that filter are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are filter names and whose values are arrays of listeners.
     *
     * @param string $name A filter name (Optional)
     *
     * @return array|null
     */
    public function getFilters($name = null)
    {
        if (!is_null($name)) {
            return isset($this->filters[(string) $name]) ? $this->filters[(string) $name] : null;
        } else {
            return $this->filters;
        }
    }

    /**
     * Clear filter listeners
     *
     * Clear all listeners for all filters. If `$name` is
     * a valid filter name, only the listeners attached
     * to that filter will be cleared.
     *
     * @param string $name A filter name (Optional)
     *
     * @return void
     */
    public function clearFilters($name = null)
    {
        if (!is_null($name) && isset($this->filters[(string) $name])) {
            $this->filters[(string) $name] = array(array());
        } else {
            foreach ($this->filters as $key => $value) {
                $this->filters[$key] = array(array());
            }
        }
    }
}
