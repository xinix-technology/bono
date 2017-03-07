<?php
namespace Bono\Http;

class Cookies {

    protected $attributes;

    protected $sets = [];

    public static function byEnvironment($vars)
    {
        return new Cookies($_COOKIE);
    }

    public function __construct(array $vars = [])
    {
        $this->attributes = $vars;
    }

    public function get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function set($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        foreach ($this->sets as $key => $value) {
            if ($value[0] === $name) {
                array_splice($this->sets, $key, 1);
                break;
            }
        }
        $this->sets[] = func_get_args();
        $this->attributes[$name] = $value;
    }

    public function remove($name)
    {
        $old = null;
        foreach ($this->sets as $key => $value) {
            if ($value[0] === $name) {
                $old = $value;
                array_splice($this->sets, $key, 1);
                break;
            }
        }

        if (null !== $old) {
            $value[1] = '';
            $value[2] = time() - 3600;
            $this->sets[] = $value;
        } else {
            $this->sets[] = [$name, '', time() - 3600];
        }

        unset($this->attributes[$name]);
    }

    public function getSets()
    {
        return $this->sets;
    }
}
