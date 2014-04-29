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
 * @subpackage Provider
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Provider;

/**
 * CLIProvider
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Provider
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class CLIProvider extends Provider
{

    /**
     * [initialize description]
     *
     * @return [type] [description]
     */
    public function initialize()
    {
        if ($this->app->config('bono.cli')) {
            $app = $this->app;

            $this->app->container->singleton(
                'environment',
                function () {
                    return \Bono\CLI\Environment::getInstance();
                }
            );

            $this->app->notFound(
                function () use ($app) {
                    $argv = $GLOBALS['argv'];
                    $app->log->error('Command not found');
                    exit(255);
                }
            );

            $this->app->error(
                function ($err) use ($app) {
                    $app->log->error('');
                    $app->log->error('Error :'. $err->getMessage());
                    $app->log->error('File  :'. $err->getFile().':'.$err->getLine());
                    $app->log->error('');
                    $app->log->error($err->getTraceAsString());
                    $app->log->error('');
                    $app->log->error('Done with errors');
                    exit(1);
                }
            );

            $commands = $this->app->config('bonocli.commands');
            if ($commands) {
                foreach ($commands as $commandClass) {
                    $command = new $commandClass();
                    $command->initialize($this->app);
                }
            }
        }
    }
}
