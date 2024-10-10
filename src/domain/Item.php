<?php

namespace LooseChallenge\domain;

class Item
{
    private ItemKey $key;
    private float $price;
    private string $selector;

    public function __construct(ItemKey $key, float $price)
    {
        $this->key = $key;
        $this->price = $price;
        $this->selector = "GET-$key->name";
    }

    public function getName(): string
    {
        return $this->key->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }
}
