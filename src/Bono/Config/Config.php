<?php

namespace Bono\Config;

use Bono\App;

class Config {

    protected $config = array();

    public function __construct() {
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
                $content = require_once($app->config('config.path') . '/' . $fileName);
                $fileName = preg_replace('/\.php$/i', '', $fileName);
                $config[$fileName] = $content;
            }
        }

        $this->config = $config;
    }

    /**
     * Get a particular value back from the config array
     * @global array $config   The config array defined in the config files
     * @param string $index    The index to fetch in dot notation
     * @return mixed
     */
    public function get($index) {
        $config = $this->config;
        foreach (explode('.', $index) as $key => $value) {
            $config = $config[$value];
        }
        return $config;
    }

    /**
     * Set a particular value from the config array
     * @global array  $config  The config array defined in the config files
     * @param  string $index   The index to fetch in dot notation
     * @param  mixed  $valed    The value you want to set
     * @return mixed
     */
    public function set($index, $value) {
        return $this->_set($this->config, $index, $value);
    }

    /**
     * Set a particular value from the config array
     * @global array  $config  The config array defined in the config files
     * @param  string $index   The index to fetch in dot notation
     * @param  mixed  $valed    The value you want to set
     * @return mixed
     */
    private function _set(array &$array, $key, $value) {
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
