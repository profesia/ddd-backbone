<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

interface DequeueDispatcherInterface
{
    public function flush(): void;
}
