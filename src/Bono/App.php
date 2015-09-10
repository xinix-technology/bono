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
use Bono\Handler\ErrorHandler;
use Bono\Handler\NotFoundHandler;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

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
        'App' => 'Bono\\App',
        'URL' => 'Bono\\Helper\\URL',
        'Theme' => 'Bono\\Theme\\Theme',
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
        $settings['bono.theme'] = 'Bono\\Theme\\DefaultTheme';
        $settings['config.path'] = '../config';
        // slim settings debug MUST BE set true to propagate exception/error to middleware
        // commonhandlermiddleware will handle this later
        $settings['debug'] = true;
        $settings['autorun'] = true;
        $settings['bono.cli'] = (PHP_SAPI === 'cli');

        if (!isset($settings['bono.debug'])) {
            $settings['bono.debug'] = ($settings['mode'] == 'development') ? true : false;
        }

        $settings['view'] = 'Bono\\View\\LayoutedView';
        $settings['bono.partial.view'] = 'Slim\\View';

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

        // this scope should not trigger any error {
        register_shutdown_function(array($this, 'shutdownHandler'));
        set_error_handler(array($this, 'errorHandler'));

        if (isset($_SERVER['HTTP_CONTENT_TYPE']) && empty($_SERVER['CONTENT_TYPE'])) {
            $_SERVER['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
        }

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
        // }

        try {
            // DO NOT add something above except you sure that it wont break
            parent::__construct($userSettings);

            $this->container->singleton('request', function ($c) {
                return new \Bono\Http\Request($c['environment']);
            });

            $this->container->singleton('response', function ($c) {
                return new \Bono\Http\Response();
            });

            $this->container->singleton('theme', function ($c) {
                $config = $c['settings']['bono.theme'];
                if (is_array($config)) {
                    $themeClass = $config['class'];
                } else {
                    $themeClass = $config;
                    $config = array();
                }

                return ($themeClass instanceof \Bono\Theme\Theme) ? $themeClass : new $themeClass($config);
            });

            $app = $this;

            $oldView = $this->view;
            $this->view = function ($c) use ($app, $oldView) {
                if ($app->theme && $view = $app->theme->getView()) {
                    return $view;
                } else {
                    return $oldView;
                }
            };

            $this->configure();

            $this->configureHandler();

            $this->configureAliases();

            $this->configureProvider();

            $this->configureMiddleware();

            $this->configureFilters();

            if ($this->config('autorun')) {
                $this->run();
            }
        } catch (\Slim\Exception\Stop $e) {
            // noop
        } catch (\Exception $e) {
            $this->configureHandler()->error($e);
        }

    }

    public function shutdownHandler()
    {
        $e = error_get_last();

        if ($e) {
            if (!($e['type'] & error_reporting())) {
                return;
            }

            $this->configureHandler()->error(new \ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));
        }
    }

    /**
     * Override callErrorHandler
     * @param  [type] $argument [description]
     * @return [type] [description]
     */
    protected function callErrorHandler($argument = null)
    {
        while (ob_get_level() > 0) {
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
                    // noop
                }
            } else {
                $this->callErrorHandler($argument);
                // noop
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
        // why I put it here because you can override the implementation
        require_once dirname(__FILE__).'/../functions.php';

        if ($this->isRunning) {
            return;
        }

        $this->isRunning = true;

        $this->add(new \Bono\Middleware\CommonHandlerMiddleware());

        $this->slimRun();
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

    public function debugMiddlewares()
    {
        $middlewares = array();
        foreach ($this->middleware as $key => $value) {
            $middlewares[] = get_class($value);
        }
        return $middlewares;
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

        date_default_timezone_set($this->config('bono.timezone') ?: 'UTC');
    }

    protected function mergeConfig(&$to, $from)
    {
        foreach ($from as $i => $value) {
            $f = explode('!', $i);
            $key = $f[0];
            $action = count($f) === 1 ? 'merge' : $f[1];

            if ($action === 'unset') {
                unset($to[$key]);
            } elseif ($action === 'set') {
                $to[$key] = $from[$i];
            } elseif (is_array($from[$key])) {
                if (!isset($to[$key]) || !is_array($to[$key])) {
                    $to[$key] = array();
                }
                $this->mergeConfig($to[$key], $from[$key]);
            } else {
                $to[$key] = $from[$key];
            }
        }
    }

    public function config($name = null, $value = null)
    {
        $numArgs = func_num_args();
        // get all configuration settings
        if ($numArgs === 0) {
            return $this->settings;
        } elseif ($numArgs === 1) {
            // if first param is array then merge new configurations
            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    $this->config($key, $value);
                }
            } else { // get single instance of configuration
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
                $this->mergeConfig($settings[$name], $value);
                // TODO use own merge strategy
                // $settings[$name] = array_merge($settings[$name], $value);
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
                $this->whoops = new Run();

                $handler = new PrettyPageHandler();
                $path = explode(DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR, __DIR__);
                $path = $path[0].'/templates/_whoops';
                $handler->setResourcesPath($path);

                $jsonResponseHandler = new JsonResponseHandler();
                $jsonResponseHandler->onlyForAjaxRequests(true);

                $appHandler = function ($err) use ($app, $handler) {
                    if (!isset($app->request)) {
                        return;
                    }

                    $template = 'error.php';
                    if ($err->getMessage() === '404 Resource not found') {
                        $template = 'notFound.php';
                    }

                    $request = $app->request;

                    // Add some custom tables with relevant info about your application,
                    // that could prove useful in the error page:
                    $handler->addDataTable('Bono Application', array(
                        'Template'         => 'Modify this page on templates/'.$template,
                        'Application Class'=> get_class($app),
                        'Charset'          => $request->headers('ACCEPT_CHARSET') ?: '<none>',
                        'Locale'           => $request->getContentCharset() ?: '<none>',
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
                    $handler->setPageTitle("Bono got whoops! There was a problem.");
                };

                $this->whoops->pushHandler($handler);

                // Add a special handler to deal with AJAX requests with an
                // equally-informative JSON response. Since this handler is
                // first in the stack, it will be executed before the error
                // page handler, and will have a chance to decide if anything
                // needs to be done.
                $this->whoops->pushHandler($jsonResponseHandler);
                $this->whoops->pushHandler($appHandler);

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
            $options = array();
            if (is_string($k)) {
                $Provider = $k;
                $options = $v ?: array();
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

            // reekoheek: hack for middleware options
            if ($options) {
                $m = new $Middleware($options);
            } else {
                $m = new $Middleware();
            }
            $m->options = $options;
            $this->add($m);
        }
    }

    protected function configureFilters()
    {
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

    public function slimRun()
    {
        // set_error_handler(array('\Slim\Slim', 'handleErrors'));

        //Apply final outer middleware layers
        // if ($this->config('debug')) {
        //     //Apply pretty exceptions only in debug to avoid accidental information leakage in production
        //     $this->add(new \Slim\Middleware\PrettyExceptions());
        // }

        $this->add(new \Slim\Middleware\ContentTypes(array(
            'multipart/form-data' => array($this, 'parseMultipartFormData'),
            'application/x-www-form-urlencoded' => array($this, 'parseFormUrlencoded')
        )));

        //Invoke middleware and application stack
        $this->middleware[0]->call();

        //Fetch status, header, and body
        list($status, $headers, $body) = $this->response->finalize();

        // Serialize cookies (with optional encryption)
        \Slim\Http\Util::serializeCookies($headers, $this->response->cookies, $this->settings);

        //Send headers
        if (headers_sent() === false) {
            //Send status
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', \Slim\Http\Response::getMessageForCode($status)));
            } else {
                header(
                    sprintf(
                        'HTTP/%s %s',
                        $this->config('http.version'),
                        \Slim\Http\Response::getMessageForCode($status)
                    )
                );
            }

            //Send headers
            foreach ($headers as $name => $value) {
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    header("$name: $hVal", false);
                }
            }
        }

        //Send body, but only if it isn't a HEAD request
        if (!$this->request->isHead()) {
            echo $body;
        }

        restore_error_handler();
    }

    public function errorHandler($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    public function parseFormUrlencoded($input)
    {
        $data = array();
        parse_str($input, $data);
        return $data;
    }

    public function parseMultipartFormData($input)
    {
        $raw_data = $input;

        if (empty($raw_data)) {
            return;
        }
        // Fetch content and determine boundary
        // $raw_data = file_get_contents('php://input');
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

        // Fetch each part
        $parts = array_slice(explode($boundary, $raw_data), 1);
        $data = array();

        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") {
                break;
            }

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                list(, $type, $name) = $matches;
                isset($matches[4]) and $filename = $matches[4];

                // handle your fields here
                switch ($name) {
                    // this is a file upload
                    case 'userfile':
                        file_put_contents($filename, $body);
                        break;

                    // default for all other files is to populate $data
                    default:
                        $data[$name] = substr($body, 0, strlen($body) - 2);
                        break;
                }
            }

        }

        return $data;
    }
}
