<?php

namespace LooseChallenge\test;

use LooseChallenge\application\CliVendingMachineUseCase;
use PHPUnit\Framework\TestCase;

class CliVendingMachineUseCaseTest extends TestCase
{
    private CliVendingMachineUseCase $useCase;

    protected function setUp(): void
    {
        $this->useCase = new CliVendingMachineUseCase();
    }

    public function test_buySodaWithExactChange()
    {
        $command = "1, 0.25, 0.25, GET-SODA";

        $result = $this->useCase->execute($command);

        $this->assertEquals("SODA", $result);
    }
}
