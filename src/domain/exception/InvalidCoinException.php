<?php

namespace LooseChallenge\domain\exception;

use Exception;
use Throwable;

class InvalidCoinException extends Exception
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct("Invalid coin value", 400, $previous);
    }
}
