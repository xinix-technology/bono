<?php

namespace Bono\Helper;

class URL {
    public static function base($uri = '') {
        return dirname($_SERVER['SCRIPT_NAME']).'/'.trim($uri, '/');
    }

    public static function site($uri = '') {
        return $_SERVER['SCRIPT_NAME'].'/'.trim($uri, '/');
    }
}