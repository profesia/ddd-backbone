<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Command\Bus\CommandBusInterface;
use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Exception\AbstractApplicationException;

class DummyCommandBus implements CommandBusInterface
{
    public function dispatch(CommandInterface $command): void
    {
    }

    public function dispatchSync(CommandInterface $command): mixed
    {
        return current($command->getPayload());
    }
}