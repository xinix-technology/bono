<?php

namespace Bono\Helper;

class URL {
    public static function base($uri = '') {
        $dir = dirname($_SERVER['SCRIPT_NAME']);
        if ($dir === '/') {
            $dir = '';
        }
        return $dir.'/'.trim($uri, '/');
    }

    public static function site($uri = '') {
        if(preg_match("/[aA]pache/", $_SERVER['SERVER_SOFTWARE']) && in_array('mod_rewrite', apache_get_modules())) {
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            if ($dir === '/') {
                $dir = '';
            }
            return $dir.'/'.trim($uri, '/');
        } else {
            return $_SERVER['SCRIPT_NAME'].'/'.trim($uri, '/');
        }
    }
}