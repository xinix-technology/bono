<?php

/**
 * Bono - PHP5 Web Framework
 *
 * MIT LICENSE
 *
 * Copyright (c) 2013 PT Sagara Xinix Solusitama
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
 * @copyright 2013 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version   0.10.0
 * @link      http://xinix.co.id/products/bono
 */
namespace Bono;

use Slim\Slim;
use Bono\Provider\ProviderRepository;

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
 * @copyright 2013 PT Sagara Xinix Solusitama
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

        try {
            parent::__construct($userSettings);
            $this->container->singleton(
                'request', function ($c) {
                    return new \Bono\Http\Request($c['environment']);
                }
            );

            $this->container->singleton(
                'response', function ($c) {
                    return new \Bono\Http\Response();
                }
            );

            $this->container->singleton(
                'theme', function ($c) {
                    $config = $c['settings']['bono.theme'];
                    if (is_array($config)) {
                        $themeClass = $config['class'];
                    } else {
                        $themeClass = $config;
                        $config = array();
                    }

                    return ($themeClass instanceOf \Bono\Theme\Theme) ? $themeClass : new $themeClass($config);
                }
            );

            $this->configureHandler();

            $this->configure();

            $this->configureAliases();

            $this->configureProvider();

            $this->configureMiddleware();

            if ($this->config('autorun')) {

                $this->run();
            }
        } catch (\Slim\Exception\Stop $e) {
            exit;
        } catch (\Exception $e) {
            if ($this->config('bono.debug')) {
                $app = $this;
                $app->config('whoops.error_page_handler', new PrettyPageHandler);
                $app->config('whoops.error_json_handler', new JsonResponseHandler);
                $app->config('whoops.error_json_handler')->onlyForAjaxRequests(true);
                $app->config('whoops.slim_info_handler', function() use ($app) {
                    try {
                        $request = $app->request();
                    } catch (RuntimeException $e) {
                        return;
                    }

                    $current_route = $app->router()->getCurrentRoute();
                    $route_details = array();
                    if ($current_route !== null) {
                        $route_details = array(
                            'Route Name'       => $current_route->getName() ?: '<none>',
                            'Route Pattern'    => $current_route->getPattern() ?: '<none>',
                            'Route Middleware' => $current_route->getMiddleware() ?: '<none>',
                        );
                    }

                    $app->config('whoops.error_page_handler')->addDataTable('Slim Application', array_merge(array(
                        'Charset'          => $request->headers('ACCEPT_CHARSET'),
                        'Locale'           => $request->getContentCharset() ?: '<none>',
                        'Application Class'=> get_class($app)
                    ), $route_details));

                    $app->config('whoops.error_page_handler')->addDataTable('Slim Application (Request)', array(
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
                });

                // Open with editor if editor is set
                $whoops_editor = $app->config('whoops.editor');
                if ($whoops_editor !== null) {
                    $app->config('whoops.error_page_handler')->setEditor($whoops_editor);
                }

                $app->config('whoops', new Run);
                $app->config('whoops')->pushHandler($app->config('whoops.error_page_handler'));
                $app->config('whoops')->pushHandler($app->config('whoops.error_json_handler'));
                $app->config('whoops')->pushHandler($app->config('whoops.slim_info_handler'));
                $app->error(array($app->config('whoops'), Run::EXCEPTION_HANDLER));

                try {
                    $app->error($e);
                } catch (\Slim\Exception\Stop $e) {
                    // Do nothing
                }
            } else {
                try {
                    $this->error($e);
                } catch (\Slim\Exception\Stop $e) {
                    // Do nothing
                }
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

        if ($this->isRunning) {
            return;
        }
        $this->isRunning = true;

        if ($this->config('bono.debug')) {
            $this->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware());
        }
        $this->add(new \Bono\Middleware\CommonHandlerMiddleware());

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
        $that = $this;
        $onNotFound = function () use ($that) {
            $that->view    = new \Slim\View();
            $templatesPath = $that->config('app.templates.path');
            $errorTemplate = $templatesPath . DIRECTORY_SEPARATOR . 'notFound.php';

            if (is_readable($errorTemplate)) {
                $that->view->setTemplatesDirectory($templatesPath);
                $that->view->display($errorTemplate, array(), 404);
            } else {
                $that->response->setStatus(404);
                echo '<html>
                <head>
                    <title>Ugly Not Found!</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
                    <style>
                        body { font-family: Arial; font-size: 14px; line-height: 1.5; color: #333 }
                        h1 { border-bottom: 1px solid #88f; font-weight: normal; }
                        label { margin-top: 10px; display: block; font-size: .8em; font-weight: bold; }
                        pre { margin: 0}
                        blockquote { font-size: .8em; font-style: italic; margin: 0; }
                        .row, .stack-trace { border: 1px solid #f88; padding: 5px; border-radius: 5px; background-color: #fee; overflow: auto; }
                    </style>
                </head>
                <body>
                    <h1>Ugly Not Found!</h1>

                    <p>Whoops! Apparently this is not the page you are looking for.</p>
                    <blockquote>Edit this page by creating templates/notFound.php</blockquote>
                </body>
                </html>';
            }

        };
        $onError = function (\Exception $exception) use ($that, $onNotFound) {

            $errorCode = 500;
            if ($exception instanceof \Bono\Exception\RestException) {
                $errorCode = $exception->getCode();
            }

            if ($errorCode == 404) {
                $onNotFound();

                return;
            }

            $that->view    = new \Slim\View();
            $templatesPath = $that->config('app.templates.path');
            $errorTemplate = $templatesPath . DIRECTORY_SEPARATOR . 'error.php';
            $errorData     = array(
                'stackTrace' => $exception->getTraceAsString(),
                'code'       => $exception->getCode(),
                'message'    => $exception->getMessage(),
                'file'       => $exception->getFile(),
                'line'       => $exception->getLine(),
            );

            if (is_readable($errorTemplate)) {
                $that->view->setTemplatesDirectory($templatesPath);
                $that->view->display($errorTemplate, $errorData, $errorCode);
            } else {
                $that->response->setStatus($errorCode);

                echo '<html>
                <head>
                    <title>Ugly Error!</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
                    <style>
                        body { font-family: Arial; font-size: 14px; line-height: 1.5; color: #333 }
                        h1 { border-bottom: 1px solid #f88; font-weight: normal; }
                        label { margin-top: 10px; display: block; font-size: .8em; font-weight: bold; }
                        pre { margin: 0}
                        blockquote { font-size: .8em; font-style: italic; margin: 0; }
                        .row, .stack-trace { border: 1px solid #f88; padding: 5px; border-radius: 5px; background-color: #fee; overflow: auto; }
                    </style>
                </head>
                <body>
                    <h1>Ugly Error!</h1>

                    <p>Something wrong happened.</p>
                    <blockquote>Edit this page by creating templates/error.php</blockquote>

                    <label>Code</label>
                    <div class="row">'.
                        '<code>'. $errorData['code'] .'</code>'.
                    '</div>

                    <label>Message</label>
                    <div class="row"><code>'.$errorData['message'].'</code></div>

                    <label>File</label>
                    <div class="row"><code>'. $errorData['file'] .'</code></div>
                    <label>Line</label>
                    <div class="row"><code>'. $errorData['line'] .'</code></div>

                    <label>Stack Trace</label>
                    <div class="stack-trace">
                        <pre>'. $errorData['stackTrace'] .'</pre>
                    </div>

                </body>
                </html>';
            }
        };

        $this->error($onError);
        $this->notFound($onNotFound);
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
            foreach (array_keys($this->filters) as $key) {
                $this->filters[$key] = array(array());
            }
        }
    }

}

require_once dirname(__FILE__).'/../functions.php';
