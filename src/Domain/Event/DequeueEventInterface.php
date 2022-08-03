<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Event;

interface DequeueEventInterface
{
    public function flush(): void;
}
