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
 * @subpackage Helper
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Helper;

use \Bono\App;

/**
 * URL
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Helper
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class URL
{

    /**
     * [base description]
     *
     * @param string $uri [description]
     *
     * @return [type] [description]
     */
    public static function base($uri = '', $relativeTo = '')
    {
        $app = App::getInstance();

        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if (isset($scheme)) {
            return $uri;
        }


        $dir = $_SERVER['SCRIPT_NAME'];
        if (substr($dir, -4) === '.php') {
            $dir = dirname($dir);
        }

        if ($dir === '/') {
            $dir = '';
        }

        if ($relativeTo === false) {
            $relativeTo = $dir;
        } elseif (!$relativeTo) {
            $relativeTo = $app->environment['slim.url_scheme'].'://'.$_SERVER['HTTP_HOST'].$dir;
        }

        return $relativeTo.'/'.trim($uri, '/');
    }

    /**
     * [site description]
     *
     * @param string $uri [description]
     *
     * @return [type] [description]
     */
    public static function site($uri = '', $relativeTo = '')
    {
        $app = App::getInstance();

        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if (isset($scheme)) {
            return $uri;
        }

        $dir = $_SERVER['SCRIPT_NAME'];

        if ($app->config('bono.prettifyURL')) {
            if (substr($dir, -4) === '.php') {
                $dir = dirname($dir);
            }
            if ($dir === '/') {
                $dir = '';
            }
        }

        if ($relativeTo === false) {
            $relativeTo = $dir;
        } elseif (!$relativeTo) {
            $relativeTo = $app->environment['slim.url_scheme'].'://'.$_SERVER['HTTP_HOST'].$dir;
        }

        return $relativeTo.'/'.trim($uri, '/');
    }

    public static function create($uri, $qs = '', $relativeTo = '')
    {
        if (empty($qs)) {
            $qs = array();
        }

        if (is_string($qs)) {
            $arrqs = array();
            parse_str($qs, $arrqs);
            $qs = $arrqs;
        }

        $uri = static::site($uri, $relativeTo);
        $q = parse_url($uri, PHP_URL_QUERY);
        if (empty($q)) {
            $uri = explode('?'.$q, $uri);
            $uri = $uri[0];
            $arrq = array();
            parse_str($q, $arrq);
            $q = $arrq;
        } else {
            $q = array();
        }

        $q = array_merge($q, $qs);

        return $uri.(($q) ? '?'.http_build_query($q) : '');
    }

    public static function current()
    {
        $app = App::getInstance();
        return $app->environment['slim.url_scheme'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }
}
