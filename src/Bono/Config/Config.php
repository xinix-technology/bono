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
 * @subpackage Config
 * @author     Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Config;

use Bono\App;

/**
 * Controller
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Controller
 * @author     Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class Config
{
    protected $config = array();

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $list = null;
        $app = App::getInstance();
        $config = array();

        try {
            $list = scandir($app->config('config.path'));
        } catch (\Exception $e) {
            throw new \Exception("The path of configuration file doesn't found", 33);
        }

        foreach ($list as $fileName) {
            if ($fileName != '.' && $fileName != '..') {
                $content = include_once $app->config('config.path') . '/' . $fileName;
                $fileName = preg_replace('/\.php$/i', '', $fileName);
                $config[$fileName] = $content;
            }
        }

        $this->config = $config;
    }

    /**
     * Get a particular value back from the config array
     *
     * @param string $index The index to fetch in dot notation
     *
     * @global array $config   The config array defined in the config files
     *
     * @return mixed
     */
    public function get($index)
    {
        $config = $this->config;
        foreach (explode('.', $index) as $value) {
            $config = $config[$value];
        }

        return $config;
    }

    /**
     * Set a particular value from the config array
     *
     * @param string $index The index to fetch in dot notation
     * @param mixed  $value The value you want to set
     *
     * @global array  $config  The config array defined in the config files
     *
     * @return mixed
     */
    public function set($index, $value)
    {
        return $this->_set($this->config, $index, $value);
    }

    /**
     * Set a particular value from the config array
     *
     * @param array &$array The array of config
     * @param mixed $key    The index of array you want to set
     * @param mixed $value  The value you wanna set
     *
     * @global array  $config  The config array defined in the config files
     *
     * @return mixed
     */
    private function _set(array &$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

}
