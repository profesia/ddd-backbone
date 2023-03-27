<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Bus;

use Profesia\DddBackbone\Application\Command\CommandInterface;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): void;
}
