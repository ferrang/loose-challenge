<?php

namespace LooseChallenge\domain;

use Illuminate\Support\Collection;

class VendingMachine
{
    public function __construct(
        /** @var Collection<ItemKey, Collection<Item>> */
        private readonly Collection $availableItems,
        /** @var Collection<Coin> */
        private Collection $availableChange,
        /** @var Collection<Coin> */
        private Collection $insertedMoney
    )
    {
    }

    public static function buildEmpty(): VendingMachine
    {
        return new VendingMachine(
            availableItems: collect(),
            availableChange: collect(),
            insertedMoney: collect()
        );
    }

    /**
     * @param Collection $money
     * @return void
     */
    public function insertMoney(Collection $money): void
    {
        $this->insertedMoney = $this->insertedMoney->merge($money);
    }

    /**
     * @param string $key of the item the customer wants
     * @return ?Item if any available
     */
    public function vendItem(string $key): ?Item
    {
        if (!$this->availableItems->has($key)) {
            return null;
        }

        // Fetch all items of that type
        $items = $this->availableItems->get($key);
        if ($this->insertedMoneyIsEnough($items->first()->getPrice())) {
            // TODO: Return unnecessary money back to user
            // Empty inserted money
            $this->insertedMoney = collect();
            // Get one item and return
            return $items->shift();
        }
        // Not enough money :(
        return null;
    }

    /**
     * @return Collection<Coin>
     */
    public function getInsertedMoney(): Collection
    {
        $insertedMoney = $this->insertedMoney;
        $this->insertedMoney = collect();
        return $insertedMoney;
    }

    /**
     * @param Collection<Item> $items
     * @param Collection<Coin> $change
     * @return void
     */
    public function service(Collection $items, Collection $change): void
    {
        $this->availableChange = $this->availableChange->merge($change);
        $items->each(fn(Item $item) => $this->availableItems->put(
            $item->getName(),
            collect([$item])->concat($this->availableItems->get($item->getName()))
        ));
    }

    private function insertedMoneyIsEnough(float $itemPrice): bool
    {
        $totalMoney = $this->insertedMoney->reduce(fn(?float $carry, Coin $item) => $carry + $item->getValue());
        return $totalMoney >= $itemPrice;
    }
}
