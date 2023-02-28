<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Test\Unit\Domain\ValueObject;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\DddBackbone\Domain\Exception\InvalidArgumentException;
use Profesia\DddBackbone\Test\Assets\TestIntId;
use Profesia\DddBackbone\Test\Assets\TestUuid4Id;
use Ramsey\Uuid\Rfc4122\UuidV4;

class IdValueObjectsTest extends MockeryTestCase
{

    public function provideIdsData(): array
    {
        $uuid = UuidV4::uuid4();

        return [
            [
                [
                    TestIntId::createFromInt(10),
                    [
                        'toString' => '10',
                        'toInt'    => 10,
                    ],
                ],
            ],
            [
                [
                    TestUuid4Id::createFromString(
                        $uuid->toString()
                    ),
                    [
                        'toString' => $uuid->toString(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $input
     *
     * @return void
     * @dataProvider provideIdsData
     */
    public function testIds(array $input): void
    {
        [$object, $dataCalls] = $input;

        foreach ($dataCalls as $methodName => $expectedValue) {
            $this->assertEquals($expectedValue, $object->{$methodName}());
        }
    }

    public function testIntIdOther(): void
    {
        $value = -1;
        $this->expectExceptionObject(
            new InvalidArgumentException("Value: [{$value}] is not a positive integer.")
        );

        TestIntId::createFromInt($value);
    }

    public function testIntIdCreation(): void
    {
        $object = TestIntId::createFromInt(20);
        $this->assertEquals('20', (string)$object);

        $otherDifferentObject = TestIntId::createFromInt(30);
        $this->assertFalse($object->equals($otherDifferentObject));
        $this->assertTrue($object->equals(TestIntId::createFromInt(20)));
    }

    public function testUuidIdOther(): void
    {
        $string = 'not-an-uuid';
        $this->expectExceptionObject(
            new InvalidArgumentException("Value: [{$string}] is not a valid string representation of an UUID")
        );

        TestUuid4Id::createFromString($string);
    }

    public function testUuidIdCreation(): void
    {
        $uuid   = UuidV4::uuid4();
        $object = TestUuid4Id::createFromString($uuid->toString());
        $this->assertEquals($uuid->toString(), (string)$object);
    }
}
