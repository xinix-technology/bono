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

if (!function_exists('l')) {
    function l($words, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL) {
        return $words;
    }
}

if (!function_exists('ll')) {
    function ll($words, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL) {
        echo l($words, $arg1, $arg2, $arg3, $arg4, $arg5);
    }
}