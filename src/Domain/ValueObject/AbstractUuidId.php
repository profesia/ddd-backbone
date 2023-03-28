<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\ValueObject;

use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;

abstract class AbstractUuidId
{
    protected string $value;

    private function __construct(
        string $value
    ) {
        $this->value = $value;
    }

    public static function createFromString(string $value): self
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException("Value: [{$value}] is not a valid string representation of an UUID");
        }

        return new static($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

}
