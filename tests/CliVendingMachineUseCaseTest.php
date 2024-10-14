<?php declare(strict_types=1);

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

    /**
     * @throws Throwable
     */
    public function test_buyOnEmptyMachine_shouldReturnErrorMessage()
    {
        $command = "1, 0.25, 0.25, GET-SODA";

        $result = $this->useCase->execute($command);

        $this->assertEquals('Product not available: No SODA available at the moment.', $result);
    }

    /**
     * @throws Throwable
     */
    public function test_buyWithInvalidCoin_shouldReturnErrorMessage()
    {
        $command = "0.50, 0.50, 0.50, GET-SODA";

        $result = $this->useCase->execute($command);

        $this->assertEquals('Invalid coin: Only 0.05, 0.1, 0.25, 1 coins are permitted.', $result);
    }

    /**
     * @throws Throwable
     */
    public function test_buyWithExactChange_shouldReturnItem()
    {
        $command = "1, 0.25, 0.25, GET-SODA";
        $this->vendingMachine->service(
            itemKeys: collect(ItemKey::SODA),
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
            itemKeys: collect(ItemKey::SODA),
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
            itemKeys: collect(ItemKey::WATER),
            change: collect()
        );

        $result = $this->useCase->execute($command);

        $this->assertEquals("WATER, 0.25, 0.10", $result);
    }
}
