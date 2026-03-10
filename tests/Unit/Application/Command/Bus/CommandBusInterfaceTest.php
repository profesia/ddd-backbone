<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application\Command\Bus;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Command\CommandInterface;
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

    public function testDispatchSyncReturnsMixedValue(): void
    {
        /** @var MockInterface|CommandInterface $command */
        $command = Mockery::mock(CommandInterface::class);

        $bus    = new NullCommandBus();
        $result = $bus->dispatchSync($command);

        $this->assertNull($result);
    }
}
