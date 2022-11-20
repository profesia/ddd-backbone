<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\ValueObject;

use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;

abstract class AbstractIntId
{
    private function __construct(
        protected int $value
    ) {
    }

    public static function createFromInt(int $value): static
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Value: [{$value}] is not a positive integer.");
        }

        return new static($value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return (string)$this->value;
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(AbstractIntId $id): bool
    {
        return $this->value === $id->value;
    }

}
