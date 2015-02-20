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
 * ControllerMiddleware
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
class ControllerMiddleware extends \Slim\Middleware
{

    /**
     * Instantiating controllers from configuration
     *
     * @return [type] [description]
     */
    public function call()
    {
        if (empty($this->options)) {
            $this->options = $this->app->config('bono.controllers');
        }

        if (empty($this->options['mapping'])) {
            return $this->next->call();
        }

        $mapping = $this->options['mapping'];

        $resourceUri = $this->app->request->getResourceUri();
        if ($mapping) {
            foreach ($mapping as $uri => $Map) {
                if (is_int($uri)) {
                    $uri = $Map;
                    $Map = null;
                }

                $matcher = preg_replace('/:\w+/', '(\w+)', '/^'.addcslashes($uri, '\/').'(?:\/.*)*$/');
                $matches = '';
                $isMatch = preg_match($matcher, $resourceUri, $matches);

                if ($isMatch)  {
                    if (is_null($Map)) {
                        if (isset($this->options['default'])) {
                            $Map = $this->options['default'];
                        } else {
                            throw new \Exception('URI "'.$uri.'" does not have suitable controller class "'.$Map.'"');
                        }
                    }
                    $this->app->controller = $controller = new $Map($this->app, $uri);
                    if (!($controller instanceof \Bono\Controller\Controller)) {
                        throw new \Exception(
                            'Controller "'.$Map.'" should be instance of Bono\Controller\Controller.'
                        );
                    }
                    break;
                }
            }
        }

        $this->next->call();
    }
}
