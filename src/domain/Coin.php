<?php

namespace LooseChallenge\domain;

use Exception;
use LooseChallenge\domain\exception\InvalidCoinException;

class Coin
{
    private static array $validValues = array(.05, .10, .25, 1);
    private float $value;

    /**
     * @throws Exception if value is not allowed
     */
    public function __construct(float $value)
    {
        if (!in_array($value, self::$validValues)) {
            throw new InvalidCoinException();
        }
        $this->value = $value;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }
}
