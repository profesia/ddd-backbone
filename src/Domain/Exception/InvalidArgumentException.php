<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Domain\Exception;

use DomainException;

class InvalidArgumentException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

