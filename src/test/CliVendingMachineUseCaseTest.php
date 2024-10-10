<?php

namespace LooseChallenge\test;

use LooseChallenge\application\CliVendingMachineUseCase;
use LooseChallenge\domain\Item;
use LooseChallenge\domain\ItemKey;
use LooseChallenge\domain\VendingMachine;
use PHPUnit\Framework\TestCase;

class CliVendingMachineUseCaseTest extends TestCase
{
    private VendingMachine $vendingMachine;
    private CliVendingMachineUseCase $useCase;

    protected function setUp(): void
    {
        $this->vendingMachine = VendingMachine::buildEmpty();
        $this->useCase = new CliVendingMachineUseCase($this->vendingMachine);
    }

    public function test_buySomethingOnEmptyMachineReturnsErrorMessage()
    {
        $command = "1, 0.25, 0.25, GET-SODA";

        $result = $this->useCase->execute($command);

        $this->assertEquals('No SODA found, please try again later.', $result);
    }

    public function test_buySodaWithExactChange()
    {
        $command = "1, 0.25, 0.25, GET-SODA";
        $this->vendingMachine->service(
            items: collect([new Item(key: ItemKey::SODA, price: 1.50)]),
            change: collect()
        );

        $result = $this->useCase->execute($command);

        $this->assertEquals("SODA", $result);
    }
}
