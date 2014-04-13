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
 * @subpackage Middleware
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Middleware;

use \Bono\Helper\URL;

/**
 * AuthMiddleware
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Middleware
 * @author     Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class AuthMiddleware extends \Slim\Middleware
{

    /**
     * [inArray description]
     *
     * @param [type] $string [description]
     * @param array  $array  [description]
     *
     * @return [type] [description]
     */
    public function inArray($string, $array = array())
    {
        if (empty($array) || empty($string)) return false;
        foreach (array_keys($array) as $key) {
            if (fnmatch($key, $string)) {
                return true;
            }
        }

        return false;
    }

    /**
     * [call description]
     *
     * @return [type] [description]
     */
    public function call()
    {
        $config = $this->app->config('auth');

        $pathInfo = $this->app->request->getPathInfo();
        $app = $this->app;

        $this->app->get(
            '/login', function () use ($app, $response) {
                $config = $this->app->config('auth');

                $selfUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::base('auth');

                $this->app->response->redirect($config['urlServiceProvider'].'login?@continue='.urlencode($selfUrl));
            }
        );

        $this->app->get(
            '/auth', function () use ($app, $response) {
                $get = $app->request->get();
                $appId = $this->app->config('appId');
                $secret = $this->app->config('secret');

                $service = array(
                    'ticket' => $get['@ticket'],
                    'appId'  => $appId,
                    'secret'  => $secret
                );

                $this->app->auth->authenticate($service);

                $this->app->response->redirect('/');
            }
        );

        $this->app->get(
            '/logout', function () use ($app, $response) {
                $config = $this->app->config('auth');

                $this->app->auth->deauthenticate();
                $this->app->auth->logout();

                $selfUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::base();

                $this->app->response->redirect($config['urlServiceProvider'].'logout?@continue='.urlencode($selfUrl));
            }
        );

        $allow = false;

        if ($this->inArray($pathInfo, $config['allow']) && ! $this->inArray($pathInfo, @$config['restricted'])) {
            $allow = true;
        }

        if (! $allow && ! $this->app->auth->check()) {
            // Redirect to login
            $selfUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::base('auth');
            $this->app->response->redirect($config['urlServiceProvider'].'login?@continue='.urlencode($selfUrl));
        } else {
            $this->next->call();
        }

    }
}
