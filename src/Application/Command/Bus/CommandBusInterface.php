<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command\Bus;

use Profesia\DddBackbone\Application\Command\CommandInterface;
use Profesia\DddBackbone\Application\Exception\AbstractApplicationException;

interface CommandBusInterface
{
    /**
     * @param CommandInterface $command
     * @return void
     * @throws AbstractApplicationException
     */
    public function dispatch(CommandInterface $command): void;

    /**
     * @param CommandInterface $command
     * @return mixed
     * @throws AbstractApplicationException
     */
    public function dispatchSync(CommandInterface $command): mixed;
}
