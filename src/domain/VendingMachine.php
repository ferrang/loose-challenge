<?php

namespace Domain;

use Illuminate\Support\Collection;

class VendingMachine
{
    /** @var Collection<ItemKey, Collection<Item>> */
    private Collection $availableItems;
    /** @var Collection<Coin> */
    private Collection $availableChange;
    /** @var Collection<Coin> */
    private Collection $insertedMoney;

    /**
     * @param string $key of the item the customer wants
     * @return bool whether there was any item of that type
     */
    public function vendItem(string $key): bool
    {
        if (!$this->availableItems->has($key)) {
            return false;
        }
        /** @var Item $item */
        $item = $this->availableItems->get($key)->shift();
        print_r($item->getName());
        return true;
    }

    /**
     * @return bool whether there was any money inserted
     */
    public function returnCoin(): bool
    {
        if ($this->insertedMoney->isEmpty()) {
            return false;
        }
        print_r($this->insertedMoney->implode(", "));
        return true;
    }

    /**
     * @param Collection<Item> $items
     * @param Collection<Coin> $change
     * @return void
     */
    public function service(Collection $items, Collection $change): void
    {
        $this->availableChange = $change;
        $items->each(fn(Item $item) => $this->availableItems->put(
            $item->getName(),
            $this->availableItems->get($item->getName())->concat([$item])
        ));
    }
}
