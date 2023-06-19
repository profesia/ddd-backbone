<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\ValueObject;

use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;

abstract class AbstractIntId
{
    protected int $value;

    private function __construct(
        int $value
    ) {
        $this->value = $value;
    }

    /**
     * @param int $value
     * @return static
     */
    public static function createFromInt(int $value): self
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
