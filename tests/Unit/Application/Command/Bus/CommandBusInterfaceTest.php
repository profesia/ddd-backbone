<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Command\Bus;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Test\Assets\DummyCommand;
use Profesia\DddBackbone\Test\Assets\DummyCommandBus;
use Profesia\DddBackbone\Test\Assets\NullCommand;
use Profesia\DddBackbone\Test\Assets\NullCommandBus;

class CommandBusInterfaceTest extends MockeryTestCase
{
    public function testDispatchReturnsVoid(): void
    {
        $this->expectNotToPerformAssertions();

        /** @var MockInterface|CommandInterface $command */
        $command = Mockery::mock(CommandInterface::class);

        $bus = new NullCommandBus();
        $bus->dispatch($command);
    }

    public function provideDataForSyncDispatch(): array
    {
        return [
            'Assert null' => [
                new DummyCommand(null), null
            ],
            'Assert true' => [
                new DummyCommand(true), true
            ],
            'Assert false' => [
                new DummyCommand(false), false
            ],
            'Assert int' => [
                new DummyCommand(1), 1
            ],
            'Assert double' => [
                new DummyCommand(1.0), 1.0
            ],
            'Assert string' => [
                new DummyCommand('testing string'), 'testing string'
            ],
            'Assert array' => [
                new DummyCommand(['test' => [1, 2, 3]]), ['test' => [1, 2, 3]]
            ],
        ];
    }

    /**
     * @param CommandInterface $command
     * @param mixed $expectedValue
     * @return void
     * @dataProvider provideDataForSyncDispatch
     */
    public function testDispatchSyncReturnsMixedValue(CommandInterface $command, mixed $expectedValue): void
    {
        $bus    = new DummyCommandBus();
        $result = $bus->dispatchSync($command);

        $this->assertEquals($expectedValue, $result);
    }
}
