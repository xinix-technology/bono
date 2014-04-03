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
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage CLI
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2013 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\CLI\Command;

/**
 * Init
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage CLI
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2013 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class Init
{
    private $_app;

    /**
     * [copy description]
     *
     * @param [type] $dir   [description]
     * @param [type] $toDir [description]
     *
     * @return [type] [description]
     */
    private function _copy($dir, $toDir)
    {
        $dirHandler = opendir($dir);
        if ($dirHandler) {
            while (false !== ($entry = readdir($dirHandler))) {
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($dir.'/'.$entry)) {
                        $this->_copy($dir.'/'.$entry, $toDir.'/'.$entry);
                    } else {
                        echo "Copying ".$toDir.'/'.$entry." ...\n";
                        @mkdir($toDir, 0777, true);
                        $fileContent = file_get_contents($dir.'/'.$entry);
                        file_put_contents($toDir.'/'.$entry, $fileContent);
                    }
                }
            }
            closedir($dirHandler);
        }
    }

    /**
     * [doInit description]
     *
     * @return [type] [description]
     */
    public function doInit()
    {
        $skelDir = './vendor/xinix-technology/bono/skel';
        if (!is_dir($skelDir) || !is_readable($skelDir)) {
            $skelDir = realpath(dirname(__FILE__).'/../../../../skel');
        }

        $this->_copy($skelDir, '.');

    }

    /**
     * [initialize description]
     *
     * @param [type] $app [description]
     *
     * @return [type] [description]
     */
    public function initialize($app)
    {
        $this->_app = $app;
        $that = $this;

        $app->get(
            '/init', function () use ($that, $app) {
                $that->doInit($app);
            }
        );
    }
}
