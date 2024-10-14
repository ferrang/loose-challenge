<?php declare(strict_types=1);

namespace LooseChallenge\domain\exception;

use Exception;
use LooseChallenge\domain\Coin;
use Throwable;

class InvalidCoinException extends Exception
{
    public function __construct(?Throwable $previous = null)
    {
        $validValues = collect(Coin::$validValues)->implode(', ');
        parent::__construct("Only $validValues coins are permitted.", 400, $previous);
    }
}
