<?php

namespace Bono\Helper;

class URL {
    public static function site($uri = '') {
        return $_SERVER['SCRIPT_NAME'].'/'.$uri;
    }
}