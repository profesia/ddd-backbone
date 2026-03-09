<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Doctrine\Exception;

use Error;
use Exception;
use ReflectionProperty;
use RuntimeException;
use Throwable;

final class RollbackFailedException extends RuntimeException
{
    /**
     * Chains $commitException as the $previous of $rollbackException, preserving its original class.
     * Falls back to a RollbackFailedException if reflection is unavailable.
     */
    public static function chain(Throwable $rollbackException, Throwable $commitException): Throwable
    {
        try {
            $baseClass = $rollbackException instanceof Exception ? Exception::class : Error::class;
            $property  = new ReflectionProperty($baseClass, 'previous');
            $property->setValue($rollbackException, $commitException);

            return $rollbackException;
        } catch (Throwable) {
            return self::createFromThrowables($rollbackException, $commitException);
        }
    }

    /**
     * Creates a RollbackFailedException wrapping the rollback exception's message/code,
     * with the commit exception chained as $previous.
     * RuntimeException only accepts int for $code; numeric string codes are converted to their
     * integer value, while non-numeric string codes (e.g. PDO SQLSTATE) are cast to 0.
     */
    public static function createFromThrowables(Throwable $rollbackException, Throwable $commitException): self
    {
        return new self(
            $rollbackException->getMessage(),
            (int) $rollbackException->getCode(),
            $commitException
        );
    }
}
