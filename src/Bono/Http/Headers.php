<?php

namespace Bono\Http;

use ROH\Util\Collection;

class Headers extends Collection
{
    protected static $special = [
        'CONTENT_TYPE' => 1,
        'CONTENT_LENGTH' => 1,
        'PHP_AUTH_USER' => 1,
        'PHP_AUTH_PW' => 1,
        'PHP_AUTH_DIGEST' => 1,
        'AUTH_TYPE' => 1,
    ];

    protected static $instance = null;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new Headers();
            foreach ($_SERVER as $key => $value) {
                $key = strtoupper($key);
                if (isset(static::$special[$key]) || strpos($key, 'HTTP_') === 0) {
                    if ($key === 'HTTP_CONTENT_LENGTH') {
                        continue;
                    }

                    $key = strtr(strtolower($key), '_', '-');
                    if (strpos($key, 'http-') === 0) {
                        $key = substr($key, 5);
                    }

                    static::$instance[$key] = array_map(function ($v) {
                        return trim($v);
                    }, explode(',', $value));
                }
            }
        }
        return static::$instance;
    }

    public function normalize()
    {
        $arr = [];
        foreach ($this as $key => $value) {
            $keyArr = explode('-', $key);
            foreach ($keyArr as &$keyToken) {
                $keyToken = strtoupper($keyToken[0]).substr($keyToken, 1);
            }
            $arr[implode('-', $keyArr)] = $value;
        }
        return $arr;
    }

    public function offsetSet($key, $value)
    {
        if (!is_array($value)) {
            $arr = $this->offsetExists($key) ? $this[$key] : [];
            $arr[] = $value;
            $value = $arr;
        }

        return parent::offsetSet(strtolower($key), $value);
    }

    public function offsetGet($key)
    {
        return parent::offsetGet(strtolower($key));
    }
}
