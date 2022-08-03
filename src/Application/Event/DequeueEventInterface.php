<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Event;

interface DequeueEventInterface
{
    public function flush(): void;
}
