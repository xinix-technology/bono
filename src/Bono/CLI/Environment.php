<?php

namespace Bono\CLI;

class Environment extends \Slim\Environment {
    private function __construct($settings = null)
    {
        if ($settings) {
            $this->properties = $settings;
        } else {

            // Virtual environment for php-cli (ignore for phpunit)
            // if(PHP_SAPI === 'cli') {

            $argv = $GLOBALS['argv'];

            array_shift( $argv );

            // Convert $argv parameters to PATH string
            $env = self::mock(array(
                'SCRIPT_NAME'   => $_SERVER['SCRIPT_NAME'],
                'PATH_INFO'     => '/'.implode( '/', $argv )
            ));

            // }

            //HTTP request headers
            $specialHeaders = array('CONTENT_TYPE', 'CONTENT_LENGTH', 'PHP_AUTH_USER', 'PHP_AUTH_PW', 'PHP_AUTH_DIGEST', 'AUTH_TYPE');
            foreach ($_SERVER as $key => $value) {
                $value = is_string($value) ? trim($value) : $value;
                if (strpos($key, 'HTTP_') === 0) {
                    $env[substr($key, 5)] = $value;
                } elseif (strpos($key, 'X_') === 0 || in_array($key, $specialHeaders)) {
                    $env[$key] = $value;
                }
            }

            //Is the application running under HTTPS or HTTP protocol?
            $env['slim.url_scheme'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

            //Input stream (readable one time only; not available for mutipart/form-data requests)
            $rawInput = @file_get_contents('php://input');
            if (!$rawInput) {
                $rawInput = '';
            }
            $env['slim.input'] = $rawInput;

            //Error stream
            $env['slim.errors'] = fopen('php://stderr', 'w');

            $this->properties = $env;
        }
    }

    public static function getInstance($refresh = false) {
        if (is_null(self::$environment) || $refresh) {
            self::$environment = new self();
        }

        return self::$environment;
    }
}