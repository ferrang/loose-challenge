<?php

namespace LooseChallenge\test;

use LooseChallenge\application\CliVendingMachineUseCase;
use LooseChallenge\domain\Coin;
use LooseChallenge\domain\Item;
use LooseChallenge\domain\ItemKey;
use LooseChallenge\domain\VendingMachine;
use PHPUnit\Framework\TestCase;
use Throwable;

class CliVendingMachineUseCaseTest extends TestCase
{
    private VendingMachine $vendingMachine;
    private CliVendingMachineUseCase $useCase;

    protected function setUp(): void
    {
        $this->vendingMachine = VendingMachine::buildEmpty();
        $this->useCase = new CliVendingMachineUseCase($this->vendingMachine);
    }

    /**
     * @throws Throwable
     */
    public function test_buyOnEmptyMachine_shouldReturnErrorMessage()
    {
        $command = "1, 0.25, 0.25, GET-SODA";

        $result = $this->useCase->execute($command);

        $this->assertEquals('No SODA found, please try again later.', $result);
    }

    /**
     * @throws Throwable
     */
    public function test_buyWithExactChange_shouldReturnItem()
    {
        $command = "1, 0.25, 0.25, GET-SODA";
        $this->vendingMachine->service(
            items: collect([new Item(key: ItemKey::SODA, price: 1.50)]),
            change: collect()
        );

        $result = $this->useCase->execute($command);

        $this->assertEquals("SODA", $result);
    }

    /**
     * @throws Throwable
     */
    public function test_buyWithExactChange_shouldLeaveVendingMachineWithMoneyAvailable()
    {
        $command = "1, 0.25, 0.25, GET-SODA";
        $this->vendingMachine->service(
            items: collect([new Item(key: ItemKey::SODA, price: 1.50)]),
            change: collect()
        );

        $result = $this->useCase->execute($command);

        $this->assertEquals("SODA", $result);
        $this->assertEmpty($this->vendingMachine->getInsertedMoney());
    }

    /**
     * @throws Throwable
     */
    public function test_returnCoin_shouldGiveBackAllCoinsInserted()
    {
        $command = "0.10, 0.10, RETURN-COIN";

        $result = $this->useCase->execute($command);

        $this->assertEquals("0.10, 0.10", $result);
        $this->assertEmpty($this->vendingMachine->getInsertedMoney());
    }

    /**
     * @throws Throwable
     */
    public function test_buyWithChange_shouldReturnExceedingMoneyBackToUser()
    {
        $command = "1, GET-WATER";
        $this->vendingMachine->service(
            items: collect([new Item(key: ItemKey::WATER, price: 0.65)]),
            change: collect()
        );

        $result = $this->useCase->execute($command);

        $this->assertEquals("WATER, 0.25, 0.10", $result);
    }
}
