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
 * LanguageProvider
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
class LanguageProvider extends Provider
{
    protected $options = array(
        'lang' => 'en-US',
        'debug' => true,
    );

    protected $language = null;

    protected $dictionaries = array();

    protected $baseDirectories = array(
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
    );

    /**
     * [initialize description]
     *
     * @return [type] [description]
     */
    public function initialize()
    {
        $lang = $this;

        if (is_callable($this->options['lang'])) {
            $this->options['lang'] = call_user_func($this->options['lang']);
        }

        if ($p = realpath(rtrim($this->app->config('bono.base.path'), DIRECTORY_SEPARATOR))) {
            $this->addBaseDirectory($p, 9);
        }

        $d = explode(DIRECTORY_SEPARATOR.'src', __DIR__);
        $this->addBaseDirectory($d[0]);

        $this->app->container->singleton('lang', function () use ($lang) {
            return $lang;
        });
    }

    public function translate($words)
    {
        $translated = $this->getTranslation($words, $this->lang());

        if (1 === func_num_args()) {
            return $translated;
        }

        $args = func_get_args();
        if (is_array($args[1])) {
            $params = $args[1];
        } else {
            $params = array_slice($args, 1);
        }

        foreach ($params as $key => $value) {
            $translated = str_replace('{'.$key.'}', $value, $translated);
        }

        return $translated;
    }

    public function lang($lang = null)
    {
        if ($lang) {
            $this->language = $lang;
            return $this;
        } else {
            return strtolower(str_replace('_', '-', trim($this->language ?: val($this->options['lang']))));
        }
    }

    public function loadLanguageDirectory($dir)
    {
        $dictionary = array();
        if (is_dir($dir)) {
            $files = glob($dir.'/*.php');
            foreach ($files as $file) {
                $arr = include($file);
                $dictionary = array_merge($dictionary, $arr);
            }
        }
        return $dictionary;
    }

    public function getTranslation($words, $lang)
    {
        $dictionary = $this->getDictionary($lang);
        if (isset($dictionary[$words])) {
            return $dictionary[$words];
        }
        return $words;
    }

    public function getDictionary($lang)
    {

        if (!isset($this->dictionaries[$lang])) {
            $fallbackLang = explode('-', $lang);
            $fallbackLang = $fallbackLang[0];

            $dictionary = array();

            foreach ($this->baseDirectories as $dirs) {
                foreach ($dirs as $dir) {
                    $dictionary = array_merge($dictionary, $this->loadLanguageDirectory($dir.'/lang/'.$fallbackLang));
                    $dictionary = array_merge($dictionary, $this->loadLanguageDirectory($dir.'/lang/'.$lang));
                }
            }

            $this->dictionaries[$lang] = $dictionary;
        }

        return $this->dictionaries[$lang];
    }

    /**
     * [addBaseDirectory description]
     *
     * @param [type]  $p        [description]
     * @param integer $priority [description]
     *
     * @return [type] [description]
     */
    public function addBaseDirectory($p, $priority = 0)
    {
        $this->baseDirectories[$priority][] = $p;
    }
}
