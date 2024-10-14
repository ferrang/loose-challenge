<?php

namespace LooseChallenge\domain\exception;

use Exception;
use Throwable;

class NotEnoughMoneyException extends Exception
{
    public function __construct(string $product, float $price, ?Throwable $previous = null)
    {
        parent::__construct("You need to insert $price to acquire $product", 400, $previous);
    }
}
