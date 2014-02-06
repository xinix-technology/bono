<?php

if (!function_exists('f')) {
    function f($name, $arg = NULL) {
        return \Bono\App::getInstance()->applyFilter($name, $arg);
    }
}

if (!function_exists('h')) {
    function h($name, $arg = NULL) {
        return \Bono\App::getInstance()->applyHook($name, $arg);
    }
}