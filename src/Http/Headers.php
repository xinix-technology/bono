<?php

namespace Bono\Http;

use ROH\Util\Collection;
use Traversable;
use Bono\Exception\BonoException;

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

    public static function byEnvironment(array $var)
    {
        $data = [];
        foreach ($var as $key => $value) {
            $key = strtoupper($key);
            if (isset(static::$special[$key]) || strpos($key, 'HTTP_') === 0) {
                if ('HTTP_CONTENT_LENGTH' !== $key && 'HTTP_COOKIE' !== $key) {
                    $k = preg_replace('/^HTTP_([A-Z_]+)$/', '$1', $key);
                    $data[str_replace('_', '-', $k)] =  $value;
                }
            }
        }

        return new static($data);
    }

    public function __construct($headers = null)
    {
        parent::__construct();

        if (null !== $headers) {
            if (is_array($headers) || $headers instanceof Traversable) {
                foreach ($headers as $key => $value) {
                    $this[$key] = $value;
                }
            } else {
                throw new BonoException('Init headers must be traversable');
            }
        }
    }

    public function normalize()
    {
        $arr = [];
        foreach ($this as $key => $value) {
            $keyArr = explode('-', $key);
            foreach ($keyArr as &$keyToken) {
                $keyToken = strtoupper($keyToken[0]) . substr($keyToken, 1);
            }
            $arr[implode('-', $keyArr)] = $value;
        }
        return $arr;
    }

    public function add($key, $value)
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                $this->add($key, $v);
            }
        } else {
            $arr = $this->offsetGet($key) ?: [];
            $arr[] = $value;
            $this->offsetSet($key, $arr);
        }
        return $this;
    }

    public function offsetExists($key)
    {
        return parent::offsetExists(strtolower($key));
    }

    public function offsetSet($key, $value)
    {
        return parent::offsetSet(strtolower($key), is_array($value) ? $value : [$value]);
    }

    public function offsetGet($key)
    {
        return parent::offsetGet(strtolower($key));
    }
}
