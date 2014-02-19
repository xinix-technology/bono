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
 * @author      Ganesha <reekoheek@gmail.com>
 * @copyright   2013 PT Sagara Xinix Solusitama
 * @link        http://xinix.co.id/products/bono
 * @license     https://raw.github.com/xinix-technology/bono/master/LICENSE
 * @package     Bono
 *
 */
namespace Bono\Middleware;

/**
 * ControllerMiddleware
 *
 * Middleware to enable controller mechanism
 *
 * ControllerMiddleware needs Bono configuration:
 *
 * <pre>
 * array (
 *     "bono.controllers" => array (
 *         'default' => '\\\\Your\\\\Default\\\\Controller\\\\Class',
 *         'mapping' => array (
 *             '/uri' => NULL, \\\\ use default controller class
 *             '/another/uri' => '\\\\Another\\\\Controller\\\\Class', \\\\ define specific controller class
 *         )
 *     )
 * )
 * </pre>
 */
class ControllerMiddleware extends \Slim\Middleware {

    /**
     * Instantiating controllers from configuration
     */
    public function call() {
        $config = $this->app->config('bono.controllers');
        $mapping = $config['mapping'];

        $resourceUri = $this->app->request->getResourceUri();
        if ($mapping) {
            foreach ($mapping as $uri => $Map) {
                if (strpos($resourceUri, $uri) === 0) {
                    if (is_null($Map)) {
                        if (isset($config['default'])) {
                            $Map = $config['default'];
                        } else {
                            throw new \Exception('URI "'.$uri.'" does not have suitable controller class "'.$Map.'"');
                        }
                    }
                    $this->app->controller = $controller = new $Map($this->app, $uri);
                    if (!$controller instanceof \Bono\Controller\IController) {
                        throw new \Exception('Controller "'.$Map.'" should be instance of \Bono\Controller\IController.');
                    }
                    break;
                }
            }
        }

        $this->next->call();
    }
}
