<?php

namespace LooseChallenge\domain\exception;

use Exception;
use Throwable;

class ProductNotAvailableException extends Exception
{
    public function __construct(string $product, ?Throwable $previous = null)
    {
        parent::__construct("No $product available at the moment.", 404, $previous);
    }
}
