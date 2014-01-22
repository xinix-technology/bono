<?php

namespace Bono\Config;

use Bono\App;

class Config {

    protected $config = array();

    public function __construct() {
        $list = null;
        $app = App::getInstance();

        try {
            $list = scandir($app->config('config.path'));
        } catch (\Exception $e) {}

        $config = array();

        if (! is_null($list)) {
            foreach ($list as $_index => $_list) {
                if ($_list != '.' && $_list != '..') {
                    $_lists = preg_replace('/\.php$/i', '', $_list);
                    $_content = include($app->config('config.path') . '/' . $_list);
                    $config[$_lists] = $_content;
                }
            }
        }

        $this->config = $config;
    }

    /**
     * Get a particular value back from the config array
     * @global array $config The config array defined in the config files
     * @param string $index The index to fetch in dot notation
     * @return mixed
     */
    public function get($index) {
        $index = explode('.', $index);
        return $this->getValue($index, $this->config);
    }

    /**
     * Navigate through a config array looking for a particular index
     * @param array $index The index sequence we are navigating down
     * @param array $value The portion of the config array to process
     * @return mixed
     */
    private function getValue($index, $value) {
        if(is_array($index) and
            count($index)) {
            $current_index = array_shift($index);
        }
        if(is_array($index) and
            count($index) and
            is_array($value[$current_index]) and
            count($value[$current_index])) {
            return $this->getValue($index, $value[$current_index]);
        } else {
            if (isset($value[$current_index])) {
                return $value[$current_index];
            }
            return null;
        }
    }

}
