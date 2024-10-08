<?php

namespace Domain;

class VendingMachine
{
    /** @var array<Item> */
    private array $availableItems = [];
    /** @var array<Coin> */
    private array $availableChange = [];
    /** @var array<Coin> */
    private array $insertedMoney = [];
}
