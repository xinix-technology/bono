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

use Bono\Exception\BonoException;
use Bono\Exception\INotifiedException;

function flattenExceptionBacktrace(\Exception $exception)
{
    $traceProperty = (new \ReflectionClass('Exception'))->getProperty('trace');
    $traceProperty->setAccessible(true);
    $flatten = function (&$value, $key) {
        if ($value instanceof \Closure) {
            $closureReflection = new \ReflectionFunction($value);
            $value = sprintf(
                '(Closure at %s:%s)',
                $closureReflection->getFileName(),
                $closureReflection->getStartLine()
            );
        } elseif (is_object($value)) {
            $value = sprintf('object(%s)', get_class($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        }
    };
    do {
        $trace = $traceProperty->getValue($exception);
        foreach ($trace as &$call) {
            array_walk_recursive($call['args'], $flatten);
        }
        $traceProperty->setValue($exception, $trace);
    } while ($exception = $exception->getPrevious());
    $traceProperty->setAccessible(false);
}

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

        if (!$this->app->config('session.preventSession') && session_id() === '') {
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

        try {
            $this->next->call();
        } catch (INotifiedException $e) {
            h('notification.error', $e);
        }

        $this->save();
    }

    public function save()
    {
        if ($errors = $this->query(array('level' => 'error'))) {
            $this->app->response->setStatus($errors[0]['status']);
        }
        $_SESSION['notification'] = $this->messages;
    }

    public function notify($level, $options)
    {
        if ($options instanceof \Exception) {
            $e = $options;

            if ($e instanceof BonoException && $e->hasChildren()) {
                $children = $e->getChildren();
                foreach ($children as $ce) {
                    $ctx = array(
                        'level' => $level,
                        'context' => '',
                        'code' => $e->getCode(),
                        'message' => $ce->getMessage(),
                        'status' => $e->getStatus(),
                        'exception' => flattenExceptionBacktrace($ce),
                    );
                    if ($ce instanceof INotifiedException) {
                        $ctx['context'] = $ce->context();
                    }
                    $this->notify($level, $ctx);
                }
            } else {
                $options = array(
                    'level' => 'error',
                    'context' => '',
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'status' => 500,
                    'exception' => flattenExceptionBacktrace($e),
                );

                if ($e instanceof BonoException) {
                    $options['status'] = $e->getStatus();
                }

                if ($e instanceof INotifiedException) {
                    $options['context'] = $e->context();
                }

                $this->notify($options['level'], $options);
            }
        } else {
            if (is_string($options)) {
                $options = array(
                    'level' => $level,
                    'context' => '',
                    'code' => 0,
                    'message' => $options,
                );
            } else {
                $options['level'] = isset($options['level']) ? $options['level'] : $level;
                $options['context'] = isset($options['context']) ? $options['context'] : '';
            }

            if (!isset($options['status'])) {
                $options['status'] = $level === 'error' ? 500 : 400;
            }

            $this->messages[$options['level']][$options['context']][] = $options;
        }
    }

    public function show($options = null)
    {

        unset($_SESSION['notification']);

        if (is_null($options)) {
            return $this->show(array('level' => 'error')) . "\n" . $this->show(array('level' => 'info'));
        }

        $messages = $this->query($options);

        if (!empty($messages)) {
            $result = '<div class="alert '.$options['level'].'"><div><p>';

            foreach ($messages as $message) {
                $result .= '<span>'.$message['message'].'</span> ';
            }

            $result .= '</p><a href="#" class="close button warning button-outline"><i class="xn xn-close"></i>Close</a></div></div>';

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
