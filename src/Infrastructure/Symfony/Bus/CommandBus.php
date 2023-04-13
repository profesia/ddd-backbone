<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Symfony\Bus;

use Profesia\DddBackbone\Application\Command\Bus\CommandBusInterface;
use Profesia\DddBackbone\Application\Command\CommandInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus implements CommandBusInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(CommandInterface $command): void
    {
        $this->bus->dispatch($command);
    }
}