<?php declare(strict_types=1);

namespace LooseChallenge\domain;

use Exception;
use Illuminate\Support\Collection;

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
     * @throws Exception
     */
    public function vendItem(string $key): ?Item
    {
        if (!$this->availableItems->has($key)) {
            return null;
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
     * @param Collection<ItemKey> $itemKeys
     * @param Collection<Coin> $change
     * @return void
     */
    public function service(Collection $itemKeys, Collection $change): void
    {
        $this->availableChange = $this->availableChange->merge($change);
        $this->buildItemsFromKeys($itemKeys)->each(fn(Item $item) => $this->availableItems->put(
            $item->getName(),
            collect([$item])->concat($this->availableItems->get($item->getName()))
        ));
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
     * @param Collection $items
     * @return Collection
     */
    private function buildItemsFromKeys(Collection $items): Collection
    {
        return $items->map(fn(ItemKey $key) => new Item($key, self::$prices[$key->name]));
    }
}
