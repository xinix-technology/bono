<?php

namespace Bono\Helper;

use \Bono\App;

class URL {
    public static function base($uri = '') {
        $dir = $_SERVER['SCRIPT_NAME'];

        if (substr($dir, -4) === '.php') {
            $dir = dirname($dir);
        }

        if ($dir === '/') {
            $dir = '';
        }
        return $dir.'/'.trim($uri, '/');
    }

    public static function site($uri = '') {
        $dir = $_SERVER['SCRIPT_NAME'];

        if (App::getInstance()->config('bono.prettifyURL')) {
            if (substr($dir, -4) === '.php') {
                $dir = dirname($dir);
            }
            if ($dir === '/') {
                $dir = '';
            }
        }

        return $dir.'/'.trim($uri, '/');
    }
}