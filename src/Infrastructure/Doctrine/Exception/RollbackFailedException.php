<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Doctrine\Exception;

use Profesia\DddBackbone\Application\Exception\TransactionServiceException;
use Throwable;

final class RollbackFailedException extends TransactionServiceException
{
    private Throwable $commitException;

    public static function createFromThrowables(Throwable $rollbackException, Throwable $commitException): self
    {
        $instance                  = new self(
            $rollbackException->getMessage(),
            (int) $rollbackException->getCode(),
            $rollbackException
        );
        $instance->commitException = $commitException;

        return $instance;
    }

    public function getCommitException(): Throwable
    {
        return $this->commitException;
    }
}
