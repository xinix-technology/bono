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
 * NotificationMiddleware
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
class NotificationMiddleware extends \Slim\Middleware
{
    protected $messages = array(
        'info' => array('' => array()),
        'error' => array('' => array()),
    );

    public function call()
    {
        $that = $this;

        if (session_id() === '') {
            throw new \Exception(
                'NotificationMiddleware needs \\Bono\\Middleware\\SessionMiddleware or php native session'
            );
        }

        $this->app->hook('notification.error', function ($options) use ($that) {
            $that->notify('error', $options);
        });

        $this->app->hook('notification.info', function ($options) use ($that) {
            $that->notify('info', $options);
        });

        $this->app->filter('notification.show', function ($options = null) use ($that) {
            return $that->show($options);
        });

        $this->app->filter('notification.message', function ($context) use ($that) {
            $errors = $that->query(array('level'=>'error', 'context' => $context));
            if (!empty($errors)) {
                return $errors[0]['message'];
            }
        });

        $this->app->notification = $this;

        $this->populate();

        $this->next->call();

        $this->save();
    }

    public function save()
    {
        $_SESSION['notification'] = $this->messages;
    }

    public function notify($level, $options)
    {
        if ($options instanceof \Exception) {
            $e = $options;
            if (method_exists($e, 'sub') && $sub = $e->sub()) {
                foreach ($sub as $e) {
                    $this->notify($level, $e);
                }

                return;
            }

            $options = array(
                'level' => 'error',
                'context' => '',
                'message' => $e->getMessage(),
            );

            if (method_exists($e, 'context')) {
                $options['context'] = $e->context();
            }
        } elseif (is_string($options)) {
            $options = array(
                'level' => $level,
                'context' => '',
                'message' => $options,
            );
        } else {
            $options['level'] = isset($options['level']) ? $options['level'] : $level;
            $options['context'] = isset($options['context']) ? $options['context'] : '';
        }

        $this->messages[$options['level']][$options['context']][] = $options;
    }

    public function show($options = null)
    {

        unset($_SESSION['notification']);

        if (is_null($options)) {
            return $this->show(array('level' => 'error')) . "\n" . $this->show(array('level' => 'info'));
        }

        $messages = $this->query($options);

        if (!empty($messages)) {

            $result = '<div class="alert '.$options['level'].'">';

            foreach ($messages as $message) {
                $result .= '<p>'.$message['message'].'</p>';
            }
            $result .= '</div>';

            return $result;
        }

    }

    public function populate()
    {
        if (isset($_SESSION['notification'])) {
            $this->messages = array_merge_recursive($this->messages, $_SESSION['notification']);
            unset($_SESSION['notification']);
        }
    }

    public function query($options)
    {
        $result = array();
        $messages = $this->messages[$options['level']];
        if (isset($options['context'])) {
            if (isset($messages[$options['context']])) {
                $result = $messages[$options['context']];
            }
        } else {
            foreach ($messages as $messageGroup) {
                foreach ($messageGroup as $message) {
                    $result[] = $message;
                }
            }
        }

        return $result;
    }
}
