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
            $exception->getCode(),
            $exception
        );
    }

    public function wrap(Throwable $exception, TransactionServiceException $previous): self
    {
        return new self(
            $exception->getMessage(),
            $exception->getCode(),
            $previous
        );
    }
}