<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Command;

interface CommandInterface
{
    /**
     * @return array<mixed>
     */
    public function getPayload(): array;
}
