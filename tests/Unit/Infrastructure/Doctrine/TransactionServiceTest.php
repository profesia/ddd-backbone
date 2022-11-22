<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Infrastructure\Doctrine;

use RuntimeException;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
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
            function () {
                return;
            }
        );
    }
}
