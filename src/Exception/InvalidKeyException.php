<?php

namespace Dipesh79\LaravelCybersource\Exception;

use Exception;

class InvalidKeyException extends Exception
{
    public function __construct($message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
