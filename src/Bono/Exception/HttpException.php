<?php
namespace Bono\Exception;

use RuntimeException;

class HttpException extends RuntimeException
{
    protected $status;

    public function __construct($message = 'Internal server error', $code = 500, $e = null)
    {
        parent::__construct($message, $code, $e);

        $this->status = $code;
    }
}
