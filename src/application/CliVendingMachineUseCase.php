<?php

namespace LooseChallenge\application;

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
        $parts = collect(explode(", ", $command));
        $money = collect();
        try {
            // Collect coins and identify action
            $action = "";
            $parts->each(function ($part) use ($money, &$action) {
                if (is_numeric($part)) {
                    // Collect coin and continue
                    $money->push(new Coin($part));
                    return true;
                } else {
                    // Found the action, exit
                    $action = $part;
                    return false;
                }
            });
            if (empty($action)) {
                return "No action identified, exiting.";
            }

            // Process action
            if (str_starts_with($action, 'GET-')) {
                // Vend
                $desiredItem = explode('-', $action)[1];
                $item = $this->vendingMachine->vendItem($desiredItem);
                if (!is_null($item)) {
                    return $item->getName();
                }
                return "No $desiredItem found, please try again later.";
            }
            if (str_starts_with($action, 'RETURN-')) {
                // Give money back
            }
            if (str_starts_with($action, 'SERVICE')) {
                // Service
            }
            // If we got here something didn't work as expected
            return "This action does not exist, apologies.";
        } catch (Throwable $e) {
            print_r("Something happened: {$e->getMessage()}");
            throw $e;
        }
    }
}
