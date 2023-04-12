<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command;

interface CommandInterface
{
    public function getPayload(): array;
}
