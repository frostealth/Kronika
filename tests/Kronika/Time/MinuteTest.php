<?php

declare(strict_types=1);

/**
 * This file is part of the Kronika package.
 *
 * (c) Ivan Kudinov <i@ikudinov.pro>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kronika\Tests\Time;

use Kronika\Time\Exception\InvalidMinute;
use Kronika\Time\Minute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Minute::class)]
final class MinuteTest extends TestCase
{
    public function testBasic(): void
    {
        $minute = Minute::of(35);

        self::assertEquals(35, $minute->value());

        self::assertNotSame($minute, Minute::of(2));
        self::assertSame($minute, Minute::of($minute));
        self::assertSame($minute, Minute::of(35));
    }

    #[TestWith([60])]
    #[TestWith([-1])]
    #[Depends('testBasic')]
    public function testInvalidValue(int $value): void
    {
        $this->expectException(InvalidMinute::class);
        Minute::of($value);
    }

    #[Depends('testBasic')]
    public function testZero(): void
    {
        $actual = Minute::zero();
        self::assertEquals(0, $actual->value());
        self::assertTrue($actual->isZero());
        self::assertFalse($actual->isLast());

        unset($actual);
        $expected = Minute::zero();
        $actual = Minute::of(0);
        self::assertSame($expected, $actual);
        self::assertSame(Minute::zero(), $actual);
        self::assertTrue($actual->isZero());
        self::assertFalse($actual->isLast());

        unset($expected, $actual);
        self::assertSame(Minute::of(0), Minute::zero());
    }

    #[Depends('testBasic')]
    public function testLast(): void
    {
        $actual = Minute::last();
        self::assertEquals(59, $actual->value());
        self::assertFalse($actual->isZero());
        self::assertTrue($actual->isLast());

        unset($actual);
        $expected = Minute::last();
        $actual = Minute::of(59);
        self::assertSame($expected, $actual);
        self::assertSame(Minute::last(), $actual);
        self::assertFalse($actual->isZero());
        self::assertTrue($actual->isLast());

        unset($expected, $actual);
        self::assertSame(Minute::of(59), Minute::last());
    }

    public static function toStringProvider(): array
    {
        return [
            [Minute::of(0), '00'],
            [Minute::of(6), '06'],
            [Minute::of(12), '12'],
            [Minute::of(35), '35'],
            [Minute::of(59), '59'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toStringProvider')]
    public function testToString(Minute $minute, string $expected): void
    {
        self::assertEquals($expected, (string)$minute);
    }

    public static function comparisonProvider(): array
    {
        return [
            [Minute::of(12), Minute::of(23), -1],
            [Minute::of(12), Minute::of(12), 0],
            [Minute::of(23), Minute::of(12), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Minute $a, Minute $b, int $expected): void
    {
        self::assertTrue($a->is($a));
        self::assertFalse($a->isNot($a));
        self::assertFalse($a->isBefore($a));
        self::assertFalse($a->isAfter($a));
        self::assertTrue($a->isBeforeOrEqualTo($a));
        self::assertTrue($a->isAfterOrEqualTo($a));

        $comparison = $a->compareTo($b);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b));
        self::assertEquals($comparison->greater(), $a->isAfter($b));
        self::assertEquals($comparison->equal(), $a->is($b));
        self::assertEquals($comparison->notEqual(), $a->isNot($b));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b));
    }
}
