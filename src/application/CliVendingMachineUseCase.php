<?php declare(strict_types=1);

namespace LooseChallenge\application;

use Exception;
use Illuminate\Support\Collection;
use LooseChallenge\domain\Coin;
use LooseChallenge\domain\exception\InvalidCoinException;
use LooseChallenge\domain\VendingMachine;
use Throwable;

class CliVendingMachineUseCase
{
    public function __construct(private readonly VendingMachine $vendingMachine)
    {
    }

    /**
     * @throws InvalidCoinException if any coin inserted was not accepted
     * @throws Throwable
     */
    public function execute(string $command): string
    {
        try {
            if ($this->isService($command)) {
                // TODO: Service
                // 1. Get money
                // 2. Get products
                return "service";
            }
            try {
                $result = $this->collectMoneyAndGetAction($command);
            } catch (InvalidCoinException $e) {
                return "Invalid coin: {$e->getMessage()}";
            }
            $money = $result['money'];
            $action = $result['action'];
            if (empty($action)) {
                return "No action identified, exiting.";
            }
            return $this->processUserAction($action, $money);
        } catch (Throwable $e) {
            print_r("Something happened: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param string $command
     * @return Collection
     * @throws Exception
     */
    public function collectMoneyAndGetAction(string $command): Collection
    {
        $result = collect([
            'action' => "",
            'money' => collect()
        ]);
        $parts = collect(explode(", ", $command));
        $parts->each(function ($part) use (&$result) {
            if (is_numeric($part)) {
                // Collect coin and continue
                $result['money']->push(new Coin((float)$part));
                return true;
            } else {
                // Found the action, exit
                $result['action'] = $part;
                return false;
            }
        });
        return $result;
    }

    private function processUserAction(string $action, Collection $money): string
    {
        $this->vendingMachine->insertMoney($money);
        if ($this->isVending($action)) {
            $desiredItem = explode('-', $action)[1];
            $item = $this->vendingMachine->vendItem($desiredItem);
            if (!is_null($item)) {
                $changeBack = $this->getChangeBackFromVendingMachine();
                return $changeBack ? "{$item->getName()}, $changeBack" : $item->getName();
            }
            return "No $desiredItem found, please try again later.";
        }
        if ($this->isGiveMoneyBack($action)) {
            return $this->getChangeBackFromVendingMachine();
        }
        // If we got here something didn't work as expected
        return "This action does not exist, apologies.";
    }

    /**
     * @param string $action
     * @return bool
     */
    private function isVending(string $action): bool
    {
        return str_starts_with($action, 'GET-');
    }

    /**
     * @param string $action
     * @return bool
     */
    private function isGiveMoneyBack(string $action): bool
    {
        return str_starts_with($action, 'RETURN-');
    }

    /**
     * @param string $action
     * @return bool
     */
    private function isService(string $action): bool
    {
        return str_starts_with($action, 'SERVICE');
    }

    /**
     * @return string
     */
    public function getChangeBackFromVendingMachine(): string
    {
        return $this->vendingMachine->getInsertedMoney()->map(
            fn(Coin $coin) => number_format($coin->getValue(), 2, '.', '')
        )->implode(', ');
    }
}
