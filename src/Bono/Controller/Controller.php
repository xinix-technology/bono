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
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Controller
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Controller;

use \ROH\Util\Inflector;
use \Bono\Helper\URL;

/**
 * Controller
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Controller
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
abstract class Controller implements IController
{
    protected $clazz;

    protected $app;

    protected $request;

    protected $response;

    protected $baseUri;

    protected $data = array();

    /**
     * [__construct description]
     *
     * @param [type] $app     [description]
     * @param [type] $baseUri [description]
     */
    public function __construct($app, $baseUri)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->response = $app->response;

        $this->baseUri = $baseUri;
        $exploded = explode('/', $baseUri);
        $clazz = $this->clazz = Inflector::classify(end($exploded));

        // DEPRECATED reekoheek: remove inside dependency of _controller to view
        // $this->data['_controller'] = $controller = $this;

        $controller = $this;

        $response = $this->response;

        $app->filter('controller', function () use ($controller) {
            return $controller;
        });

        $app->filter('controller.name', function () use ($clazz) {
            return $clazz;
        });

        $app->filter('controller.uri', function ($uri) use ($controller, $app) {
            if (strpos($uri, ':id')) {
                $params = $app->router->getCurrentRoute()->getParams();
                $uri = str_replace(':id', $params['id'] ?: 'null', $uri);
            }

            return $controller->getBaseUri().$uri;
        });

        $app->filter('controller.url', function ($uri) use ($controller, $app) {
            return URL::site(f('controller.uri', $uri)).
                ($app->environment['QUERY_STRING'] ? '?'.$app->environment['QUERY_STRING'] : '');
        });

        $app->filter('controller.redirectUrl', function ($uri) use ($controller) {
            return $controller->getRedirectUri();
        });

        $app->hook('bono.controller.before', function ($options) use ($app, $controller, $response) {
            $template = trim($controller->getBaseUri(), '/').'/'.$options['method'];
            $response->template($template);
        });

        $app->hook('bono.controller.after', function ($options) use ($app, $controller, $response) {
            $response->set($controller->getData());
        });

        $this->mapRoute();
    }

    /**
     * Get class name of controller
     * @return string Class name
     */
    public function getClass()
    {
        return $this->clazz;
    }

    /**
     * [getData description]
     *
     * @return [type] [description]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * [set description]
     *
     * @param [type] $key   [description]
     * @param [type] $value [description]
     *
     * @return [type] [description]
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * [delegate description]
     *
     * @param [type] $method [description]
     * @param array  $args   [description]
     *
     * @return [type] [description]
     */
    public function delegate($method, $args = array())
    {
        $options = array(
            'method' => $method,
            'controller' => $this,
        );
        $this->app->applyHook('bono.controller.before', $options, 1);

        $argCount = count($args);
        switch ($argCount) {
            case 0:
                $this->$method();
                break;
            case 1:
                $this->$method($args[0]);
                break;
            case 2:
                $this->$method($args[0], $args[1]);
                break;
            case 3:
                $this->$method($args[0], $args[1], $args[2]);
                break;
            case 4:
                $this->$method($args[0], $args[1], $args[2], $args[3]);
                break;
            case 5:
                $this->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
                break;
            default:
                call_user_func_array(array($this, $method), $args);
        }
        $this->app->applyHook('bono.controller.after', $options, 20);
    }

    /**
     * [map description]
     *
     * @param [type] $uri    [description]
     * @param [type] $method [description]
     *
     * @return [type] [description]
     */
    public function map($uri, $method)
    {
        if ($uri === '/') {
            $uri = '';
        }

        $controller = $this;

        return $this->app->map(
            $this->baseUri.$uri,
            function () use ($controller, $method) {
                $controller->delegate($method, func_get_args());
            }
        );
    }

    /**
     * [getBaseUri description]
     *
     * @return [type] [description]
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * [redirect description]
     *
     * @param [type]  $url    [description]
     * @param integer $status [description]
     *
     * @return [type] [description]
     */
    public function redirect($url, $status = 302)
    {
        $this->app->redirect($url, $status);
    }

    /**
     * [flash description]
     *
     * @param [type] $key   [description]
     * @param [type] $value [description]
     *
     * @return [type] [description]
     */
    public function flash($key, $value)
    {
        $this->app->flash($key, $value);
    }

    /**
     * [flashNow description]
     *
     * @param [type] $key   [description]
     * @param [type] $value [description]
     *
     * @return [type] [description]
     */
    public function flashNow($key, $value)
    {
        $this->app->flashNow($key, $value);
    }

    /**
     * [flashKeep description]
     *
     * @return [type] [description]
     */
    public function flashKeep()
    {
        $this->app->flashKeep();
    }

    public function getRedirectUri()
    {
        return URL::redirect($this->getBaseUri());
    }

    /**
     * [mapRoute description]
     *
     * @return [type] [description]
     */
    abstract public function mapRoute();
}
