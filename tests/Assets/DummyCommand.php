<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\DddBackbone\Application\Command\CommandInterface;

class DummyCommand implements CommandInterface
{
    private mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public function getPayload(): array
    {
        return [
            $this->data
        ];
    }
}