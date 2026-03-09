<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Infrastructure\Doctrine;

use RuntimeException;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\Exception\TransactionServiceException;
use Profesia\DddBackbone\Infrastructure\Doctrine\Exception\RollbackFailedException;
use Profesia\DddBackbone\Infrastructure\Doctrine\TransactionService;

class TransactionServiceTest extends MockeryTestCase
{
    public function testCanStart(): void
    {
        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('beginTransaction')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $transactionService->start();
    }

    public function testCanRollback(): void
    {
        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('rollback')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $transactionService->rollback();
    }

    public function testCanCommit(): void
    {
        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('flush')
            ->once();
        $entityManager
            ->shouldReceive('commit')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $transactionService->commit();
    }

    public function testCanThrowAnExceptionDuringTransactionRun(): void
    {
        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('beginTransaction')
            ->once();
        $entityManager
            ->shouldReceive('rollback')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $exception = new RuntimeException('Testing exception');

        $this->expectExceptionObject($exception);
        $transactionService->transactional(
            function () use ($exception) {
                throw $exception;
            }
        );
    }

    public function testCanCommitTransaction(): void
    {
        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('beginTransaction')
            ->once();
        $entityManager
            ->shouldReceive('flush')
            ->once();
        $entityManager
            ->shouldReceive('commit')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $transactionService->transactional(
            function () {}
        );
    }

    public function testCanReturnValueFromTransaction(): void
    {
        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('beginTransaction')
            ->once();
        $entityManager
            ->shouldReceive('flush')
            ->once();
        $entityManager
            ->shouldReceive('commit')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $expectedValue = 'test';
        $actualValue = $transactionService->transactional(
            function () use ($expectedValue) {
                return $expectedValue;
            }
        );

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testWillWrapCommitExceptionInTransactionServiceException(): void
    {
        $commitException = new RuntimeException('Exception during commit', 5);

        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('beginTransaction')
            ->once();
        $entityManager
            ->shouldReceive('flush')
            ->once()
            ->andThrow($commitException);
        $entityManager
            ->shouldReceive('rollback')
            ->once();

        $transactionService = new TransactionService(
            $entityManager
        );

        $this->expectException(TransactionServiceException::class);
        $this->expectExceptionMessage($commitException->getMessage());

        try {
            $transactionService->transactional(
                function () {}
            );
        } catch (TransactionServiceException $e) {
            $this->assertSame($commitException, $e->getPrevious());
            $this->assertEquals($commitException->getCode(), $e->getCode());

            throw $e;
        }
    }

    public function testWillChainExceptionWhenRollbackAlsoFails(): void
    {
        $commitException   = new RuntimeException('Exception during commit');
        $rollbackException = new RuntimeException('Exception during rollback');

        /** @var MockInterface|EntityManagerInterface $entityManager */
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('beginTransaction')
            ->once();
        $entityManager
            ->shouldReceive('flush')
            ->once()
            ->andThrow($commitException);
        $entityManager
            ->shouldReceive('rollback')
            ->once()
            ->andThrow($rollbackException);

        $transactionService = new TransactionService(
            $entityManager
        );

        $this->expectException(RollbackFailedException::class);
        $this->expectExceptionMessage($rollbackException->getMessage());

        try {
            $transactionService->transactional(
                function () {}
            );
        } catch (RollbackFailedException $e) {
            $this->assertSame($rollbackException, $e->getPrevious());
            $this->assertSame($commitException, $e->getCommitException());

            throw $e;
        }
    }
}
