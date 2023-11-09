<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

use Profesia\DddBackbone\Domain\Event\DispatcherInterface;

interface DequeueDispatcherInterface extends DispatcherInterface
{
    public function flush(): void;

    public function clear(): void;
}
