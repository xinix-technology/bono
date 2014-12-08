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

/**
 * SessionMiddleware
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
class SessionMiddleware extends \Slim\Middleware
{

    /**
     * Call method of SessionMiddleware
     *
     * @return void
     */
    public function call()
    {
        $defaultOptions = array(
            'name' => 'BSESS',
            'lifetime' => 0,
            'path' => \URL::base('', false),
            'domain' => '',
            'secure' => false,
            'httpOnly' => false,
        );

        if (is_array($this->options)) {
            $this->options = array_merge($defaultOptions, $this->options);
        } else {
            $this->options = $defaultOptions;
        }
        $this->app->session = $this;

        if (!$this->app->config('session.preventSession')) {
            $this->start();
        }

        $this->next->call();
    }

    public function start($options = array())
    {
        $options = array_merge($this->options, $options);

        if (isset($_COOKIE['keep'])) {
            $options['lifetime'] = $_COOKIE['keep'];
        }

        session_set_cookie_params(
            $options['lifetime'],
            $options['path'],
            $options['domain'],
            $options['secure'],
            $options['httpOnly']
        );
        session_name($options['name']);
        session_start();

        if (!empty($options['lifetime'])) {
            setcookie(session_name(), session_id(), time() + $options['lifetime'], $options['path']);
            setcookie('keep', $options['lifetime'], time() + $options['lifetime'], $options['path']);
        } else {
            setcookie(session_name(), session_id(), 0, $options['path']);
        }
    }

    public function destroy()
    {
        unset($_SESSION);
        session_destroy();
        unset($_COOKIE['keep']);
        setcookie($this->options['name'], '', time() - 3600, $this->options['path']);
        setcookie('keep', '', time() - 3600, $this->options['path']);
    }

    public function reset($options = array())
    {
        $this->destroy();
        $this->start($options);
        // session_regenerate_id(true);
    }
}
