<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Infrastructure\Doctrine\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Profesia\DddBackbone\Infrastructure\Doctrine\Exception\RollbackFailedException;

class RollbackFailedExceptionTest extends TestCase
{
    public function testCanCreateFromThrowables(): void
    {
        $commitException   = new RuntimeException('Exception during commit', 1);
        $rollbackException = new RuntimeException('Exception during rollback', 2);

        $exception = RollbackFailedException::createFromThrowables($rollbackException, $commitException);

        $this->assertInstanceOf(RollbackFailedException::class, $exception);
        $this->assertEquals($rollbackException->getMessage(), $exception->getMessage());
        $this->assertEquals($rollbackException->getCode(), $exception->getCode());
        $this->assertSame($commitException, $exception->getPrevious());
    }

    public function testChainPreservesOriginalExceptionClass(): void
    {
        $commitException   = new RuntimeException('Exception during commit');
        $rollbackException = new RuntimeException('Exception during rollback', 5);

        $chained = RollbackFailedException::chain($rollbackException, $commitException);

        $this->assertSame($rollbackException, $chained);
        $this->assertInstanceOf(RuntimeException::class, $chained);
        $this->assertNotInstanceOf(RollbackFailedException::class, $chained);
        $this->assertEquals($rollbackException->getMessage(), $chained->getMessage());
        $this->assertSame($commitException, $chained->getPrevious());
    }
}
