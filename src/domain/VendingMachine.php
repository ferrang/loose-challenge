<?php

namespace LooseChallenge\domain;

use Illuminate\Support\Collection;

class VendingMachine
{
    public function __construct(
        /** @var Collection<ItemKey, Collection<Item>> */
        private readonly Collection $availableItems,
        /** @var Collection<Coin> */
        private readonly Collection $availableChange,
        /** @var Collection<Coin> */
        private readonly Collection $insertedMoney
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
     * @param string $key of the item the customer wants
     * @return ?Item if any available
     */
    public function vendItem(string $key): ?Item
    {
        if (!$this->availableItems->has($key)) {
            return null;
        }
        return $this->availableItems->get($key)->shift();
    }

    /**
     * @return Collection<Coin>
     */
    public function returnCoin(): Collection
    {
        return $this->insertedMoney;
    }

    /**
     * @param Collection<Item> $items
     * @param Collection<Coin> $change
     * @return void
     */
    public function service(Collection $items, Collection $change): void
    {
        $this->availableChange->merge($change);
        $items->each(fn(Item $item) => $this->availableItems->put(
            $item->getName(),
            collect([$item])->concat($this->availableItems->get($item->getName()))
        ));
    }
}
