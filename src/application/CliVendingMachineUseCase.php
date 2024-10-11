<?php

namespace LooseChallenge\application;

use Exception;
use Illuminate\Support\Collection;
use LooseChallenge\domain\Coin;
use LooseChallenge\domain\VendingMachine;
use Throwable;

class CliVendingMachineUseCase
{
    public function __construct(private readonly VendingMachine $vendingMachine)
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(string $command): string
    {
        try {
            $result = $this->collectMoneyAndGetAction($command);
            $money = $result['money'];
            $action = $result['action'];
            if (empty($action)) {
                return "No action identified, exiting.";
            }
            return $this->processAction($action, $money);
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
                $result['money']->push(new Coin($part));
                return true;
            } else {
                // Found the action, exit
                $result['action'] = $part;
                return false;
            }
        });
        return $result;
    }

    private function processAction(string $action, Collection $money): string
    {
        if ($this->isVending($action)) {
            $desiredItem = explode('-', $action)[1];
            $item = $this->vendingMachine->vendItem($desiredItem, $money);
            if (!is_null($item)) {
                return $item->getName();
            }
            return "No $desiredItem found, please try again later.";
        }
        if ($this->isGiveMoneyBack($action)) {
            // TODO: Give money back
        }
        if ($this->isService($action)) {
            // TODO: Service
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
}
