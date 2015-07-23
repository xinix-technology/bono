<?php

namespace Bono\Exception;

class BonoException extends \RuntimeException
{
    protected $status = 500;

    protected $children = array();

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }
}
