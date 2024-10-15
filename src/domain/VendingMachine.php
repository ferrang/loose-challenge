<?php declare(strict_types=1);

namespace LooseChallenge\domain;

use Exception;
use Illuminate\Support\Collection;
use LooseChallenge\domain\exception\NotEnoughMoneyException;
use LooseChallenge\domain\exception\ProductNotAvailableException;

class VendingMachine
{
    private static array $prices = [
        'WATER' => .65,
        'JUICE' => 1,
        'SODA' => 1.5,
    ];

    /** @var Collection<ItemKey, Collection<Item>> */
    private Collection $availableItems;

    /** @var Collection<Coin> */
    private Collection $insertedMoney;

    public function __construct(
        /** @var Collection<ItemKey> */
        readonly Collection $itemKeys,
        /** @var Collection<Coin> */
        private Collection  $availableChange,
    )
    {
        $this->availableItems = $this->buildItemsFromKeys($itemKeys);
        $this->insertedMoney = collect();
    }

    public static function buildEmpty(): VendingMachine
    {
        return new VendingMachine(
            itemKeys: collect(),
            availableChange: collect(),
        );
    }

    /**
     * @param Collection<Coin> $money
     * @return void
     */
    public function insertMoney(Collection $money): void
    {
        $this->insertedMoney = $this->insertedMoney->merge($money);
    }

    /**
     * @param string $selector of the item the customer wants
     * @return ?Item if any available
     * @throws ProductNotAvailableException|NotEnoughMoneyException
     * @throws Exception
     */
    public function vendItem(string $selector): ?Item
    {
        $key = explode('-', $selector)[1];
        if (!$this->availableItems->has($key)) {
            throw new ProductNotAvailableException($key);
        }

        // Fetch all items of that type
        $items = $this->availableItems->get($key);
        $itemPrice = $items->first()->getPrice();
        if ($this->insertedMoneyIsEnough($itemPrice)) {
            // Discount item price from inserted money
            $changeBack = $this->getRawInsertedMoneyAmount() - $itemPrice;
            $this->insertedMoney = $this->calculateLeftoverCoins($changeBack);
            // Get one item and return
            return $items->shift();
        }

        throw new NotEnoughMoneyException($key, $itemPrice);
    }

    /**
     * @return Collection<ItemKey, Int> map of key to number of items for that key
     */
    public function getAvailableItems(): Collection
    {
        if ($this->availableItems->isEmpty()) return collect();
        return $this->availableItems->map(fn(Collection $items) => $items->count());
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
     * @return Collection<Coin>
     */
    public function getAvailableChange(): Collection
    {
        return $this->availableChange;
    }

    /**
     * @param Collection<ItemKey> $itemKeys
     * @param Collection<Coin> $change
     * @return void
     */
    public function service(Collection $itemKeys, Collection $change): void
    {
        $this->availableChange = $this->availableChange->merge($change);
        $this->buildItemsFromKeys($itemKeys)->each(function (Item $item): void {
            if ($this->availableItems->has($item->getName())) {
                $this->availableItems[$item->getName()] = $this->availableItems[$item->getName()]->merge([$item]);
            } else {
                $this->availableItems[$item->getName()] = collect([$item]);
            }
        });
    }

    private function insertedMoneyIsEnough(float $itemPrice): bool
    {
        return $this->getRawInsertedMoneyAmount() >= $itemPrice;
    }

    private function getRawInsertedMoneyAmount(): float
    {
        return (float)$this->insertedMoney->reduce(fn(?float $carry, Coin $item) => $carry + $item->getValue());
    }

    /**
     * @param float $amount
     * @return Collection<Coin>
     * @throws Exception
     */
    private function calculateLeftoverCoins(float $amount): Collection
    {
        $validCoins = Coin::$validValues;
        $result = collect();
        // Sort coins in descending order to start with the largest
        rsort($validCoins);
        // Convert the amount to cents to avoid floating-point precision issues
        $amountInCents = round($amount * 100);
        foreach ($validCoins as $coinValue) {
            $coinInCents = round($coinValue * 100);
            while ($amountInCents >= $coinInCents) {
                $result->push(new Coin($coinValue));
                $amountInCents -= $coinInCents;
            }
        }

        return $result;
    }

    /**
     * @param Collection $keys
     * @return Collection
     */
    private function buildItemsFromKeys(Collection $keys): Collection
    {
        if ($keys->isEmpty()) return collect();
        return $keys->map(fn(ItemKey $key) => new Item($key, self::$prices[$key->name]));
    }
}
