<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Infrastructure\Doctrine\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Profesia\DddBackbone\Application\Exception\AbstractInfrastructureException;
use Profesia\DddBackbone\Application\Exception\TransactionServiceException;
use Profesia\DddBackbone\Infrastructure\Doctrine\Exception\RollbackFailedException;

class RollbackFailedExceptionTest extends TestCase
{
    public function testCanCreateFromThrowables(): void
    {
        $commitException   = new RuntimeException('Exception during commit', 1);
        $rollbackException = new RuntimeException('Exception during rollback', 2);

        $exception = RollbackFailedException::createFromThrowables($rollbackException, $commitException);

        $this->assertInstanceOf(RollbackFailedException::class, $exception);
        $this->assertInstanceOf(TransactionServiceException::class, $exception);
        $this->assertInstanceOf(AbstractInfrastructureException::class, $exception);
        $this->assertEquals($rollbackException->getMessage(), $exception->getMessage());
        $this->assertEquals($rollbackException->getCode(), $exception->getCode());
        $this->assertSame($rollbackException, $exception->getPrevious());
        $this->assertSame($commitException, $exception->getCommitException());
    }
}
