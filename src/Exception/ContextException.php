<?php
namespace Bono\Exception;

class ContextException extends BonoException
{
    protected $status;

    public function __construct($status = 500, $message = 'Internal server error', $error = null)
    {
        $this->status = $status;

        parent::__construct($message, $status, $error);
    }

    public function getStatusCode()
    {
        return $this->status;
    }
}
