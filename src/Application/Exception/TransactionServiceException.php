<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Application\Exception;

use Throwable;

final class TransactionServiceException extends AbstractInfrastructureException
{
    public static function createFromThrowable(Throwable $exception): self
    {
        return new self(
            $exception->getMessage(),
            (int) $exception->getCode(),
            $exception
        );
    }
}