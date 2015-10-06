<?php
namespace Bono;

class Middleware
{
    protected $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
}
