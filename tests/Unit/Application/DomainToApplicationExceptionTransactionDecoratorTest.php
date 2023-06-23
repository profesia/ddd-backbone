<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Application;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\DddBackbone\Application\DomainToApplicationExceptionTransactionDecorator;
use Profesia\DddBackbone\Application\Exception\AbstractApplicationException;
use Profesia\DddBackbone\Application\Exception\BadConfigurationException;
use Profesia\DddBackbone\Application\Exception\GenericApplicationException;
use Profesia\DddBackbone\Application\TransactionServiceInterface;
use Profesia\DddBackbone\Test\Assets\TestApplicationException;
use Profesia\DddBackbone\Test\Assets\TestDomainException;

class DomainToApplicationExceptionTransactionDecoratorTest extends MockeryTestCase
{
    public function testCanStart(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('start')
            ->once();

        $decorator = new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
        );

        $decorator->start();
    }

    public function testCanCommit(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('commit')
            ->once();

        $decorator = new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
        );

        $decorator->commit();
    }

    public function testCanRollback(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('rollback')
            ->once();

        $decorator = new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
        );

        $decorator->rollback();
    }

    public function testCanPerformTransaction(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('rollback')
            ->once();

        $decorator = new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
        );

        $decorator->rollback();
    }

    public function testCanConvertDomainExceptionToApplication(): void
    {
        $callback = function () {
            return 1;
        };

        $message   = 'Domain exception message';
        $exception = new TestDomainException($message);
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('transactional')
            ->once()
            ->withArgs(
                [
                    $callback
                ]
            )
            ->andThrow(
                $exception
            );

        $decorator = new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
        );

        $this->expectExceptionObject(
            new GenericApplicationException(
                $message
            )
        );
        $decorator->transactional($callback);
    }

    public function testCanOverrideApplicationExceptionClass(): void
    {
        $callback = function () {
            return 1;
        };

        $message   = 'Domain exception message';
        $exception = new TestDomainException($message);
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldReceive('transactional')
            ->once()
            ->withArgs(
                [
                    $callback
                ]
            )
            ->andThrow(
                $exception
            );

        $decorator = new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
            TestApplicationException::class
        );

        $this->expectExceptionObject(
            new TestApplicationException(
                $message
            )
        );
        $decorator->transactional($callback);
    }

    public function testCanDetectNonExistingClass(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldNotHaveReceived();

        $this->expectExceptionObject(
            new BadConfigurationException("Class: [NonExistingClass] does not exist")
        );
        new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
            'NonExistingClass'
        );
    }

    public function testCanDetectNotDescendingClass(): void
    {
        /** @var MockInterface|TransactionServiceInterface $decoratedObject */
        $decoratedObject = Mockery::mock(TransactionServiceInterface::class);
        $decoratedObject
            ->shouldNotHaveReceived();

        $testClass   = TestDomainException::class;
        $parentClass = AbstractApplicationException::class;
        $this->expectExceptionObject(
            new BadConfigurationException("Class: [$testClass] is not a subclass of [$parentClass] class")
        );
        new DomainToApplicationExceptionTransactionDecorator(
            $decoratedObject,
            $testClass
        );
    }
}