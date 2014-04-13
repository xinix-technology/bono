<?php

namespace Bono\Exception;

class FatalException extends \Exception
{
    public function __construct($fatalError = null)
    {
        if (is_null($fatalError)) {
            $fatalError = error_get_last();
        }

        $this->message = $fatalError['message'];
        $this->code = $fatalError['type'];
        $this->file = $fatalError['file'];
        $this->line = $fatalError['line'];

    }
}
