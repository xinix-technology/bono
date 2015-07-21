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

/**
 * ContentNegotiatorMiddleware
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
class ContentNegotiatorMiddleware extends \Slim\Middleware
{
    protected $handler;
    protected $mediaType;

    /**
     * [call description]
     *
     * @return [type] [description]
     */
    public function call()
    {
        $app = $this->app;

        $this->init();

        if ($this->hasHandler()) {
            // why should prevent session?
            // $this->app->config('session.preventSession', true);

            $app->error(array($this, 'error'));
            $app->notFound(array($this, 'notFound'));
            $this->next->call();
            if ($this->app->notification) {
                $errors = $this->app->notification->query(array('level' => 'error'));
                if (!empty($errors)) {
                    $error = $errors[0];
                    if (isset($error['exception'])) {
                        throw $error['exception'];
                    } else {
                        $ex = new BonoException($error['message'], $error['code'], $error['exception']);
                        $ex->setStatus($error['status']);
                        throw $ex;
                    }
                }
            }
            $this->render();
        } else {
            $this->next->call();
        }

    }

    public function init()
    {
        $app = $this->app;

        if (empty($this->options)) {
            $this->options = $app->config('bono.contentNegotiator');
        }

        if (isset($this->options['extensions'])) {
            $app->request->addMediaTypeExtensions($this->options['extensions']);
        }

        $this->mediaType = $app->request->getMediaType();

        if (isset($this->options['views'][$this->mediaType])) {
            $this->handler = $this->options['views'][$this->mediaType];
        }
    }

    public function hasHandler()
    {
        return isset($this->handler);
    }

    public function render()
    {
        $app = $this->app;

        $app->response->setBody('');
        $app->view($this->handler);
        $app->response->headers['content-type'] = $this->mediaType;
        $app->render($app->response->template(), $app->response->data());
        $app->stop();
    }

    public function error($e)
    {
        if ($e instanceof BonoException) {
            $this->app->response->setStatus($e->getStatus());
        }

        $error = array(
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        );
        if ($this->app->config('bono.debug') === true) {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
            $error['trace'] = $e->getTrace();
        }

        $this->app->response->data(null);
        $this->app->response->data('error', $error);

        $this->render();
    }

    public function notFound()
    {
        $this->app->response->status(404);
        $error = array(
            'code' => 404,
            'message' => 'Resource not found',
        );

        $this->app->response->data(null);
        $this->app->response->data('error', $error);

        $this->render();
    }
}
