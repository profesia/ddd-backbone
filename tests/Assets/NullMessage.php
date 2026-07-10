<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Assets;

use Profesia\MessagingCoreContracts\Broking\Dto\Sending\MessageInterface;

final class NullMessage implements MessageInterface
{
    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $topic,
        private readonly array $attributes = [],
        private readonly array $data = [],
    ) {
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'topic'      => $this->topic,
            'attributes' => $this->attributes,
            'data'       => $this->data,
        ];
    }

    public function encode(): array
    {
        return $this->toArray();
    }
}
