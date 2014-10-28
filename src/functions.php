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
 * @category  PHP_Framework
 * @package   Bono
 * @author    Ganesha <reekoheek@gmail.com>
 * @copyright 2014 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version   0.10.0
 * @link      http://xinix.co.id/products/bono
 */

if (!function_exists('f')) {
    /**
     * Some f description
     *
     * @param [type] $name [description]
     * @param [type] $arg  [description]
     *
     * @return [type] [description]
     */
    function f($name, $arg = null)
    {
        return \Bono\App::getInstance()->applyFilter($name, $arg);
    }
}

if (!function_exists('salt')) {
    function salt($value)
    {
        $config = \Bono\App::getInstance()->config('bono.salt');

        if (is_string($config)) {
            $config = array(
                'salt' => $config,
                'method' => 'md5',
            );
        } else {
            $config['method'] = (isset($config['method'])) ? $config['method'] : 'md5';
        }

        if (empty($config['salt'])) {
            throw new \Exception('You should define config bono.salt in order to use salt.');
        }

        if ($value) {
            $hash = $config['method'];

            return $hash($value.$config['salt']);
        }
    }
}

if (!function_exists('h')) {
    /**
     * Some h description
     *
     * @param [type] $name [description]
     * @param [type] $arg  [description]
     *
     * @return [type] [description]
     */
    function h($name, $arg = null)
    {
        return \Bono\App::getInstance()->applyHook($name, $arg);
    }
}

if (!function_exists('l')) {
    /**
     * [l description]
     *
     * @param [type] $words [description]
     *
     * @return [type] [description]
     */
    function l($words)
    {
        $lang = \Bono\App::getInstance()->lang;
        if (is_null($lang)) {
            return $words;
        }
        return call_user_func_array(array($lang, 'translate'), func_get_args());
    }
}

if (!function_exists('ll')) {
    /**
     * [ll description]
     * @param  [type] $words  [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    function ll()
    {
        echo call_user_func_array('l', func_get_args());
    }
}

if (!function_exists('val')) {
    /**
     * Get value from data
     * @param  [type] $data [description]
     * @return [type] [description]
     */
    function val($data)
    {
        if ($data instanceof \Closure) {
            return $data();
        }

        return $data;
    }
}
