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
        return $_SERVER['SCRIPT_NAME'].'/'.trim($uri, '/');
    }
}