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

namespace Kronika\Tests\Date;

use Kronika\Date\DayOfMonth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(DayOfMonth::class)]
final class DayOfMonthTest extends TestCase
{
    public function testBasic(): void
    {
        $day = DayOfMonth::of(15);

        self::assertEquals(15, $day->number());
        self::assertTrue($day->is(15));

        self::assertNotSame($day, DayOfMonth::of(2));
        self::assertSame($day, DayOfMonth::of(15));
        self::assertSame($day, DayOfMonth::of($day));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\AssertionError::class);
        DayOfMonth::of(32);

        $this->expectException(\AssertionError::class);
        DayOfMonth::of(1212);

        $this->expectException(\AssertionError::class);
        DayOfMonth::of(0);

        $this->expectException(\AssertionError::class);
        DayOfMonth::of(-1);
    }

    public static function toStringProvider(): array
    {
        return [
            [DayOfMonth::of(1), '01'],
            [DayOfMonth::of(6), '06'],
            [DayOfMonth::of(12), '12'],
            [DayOfMonth::of(25), '25'],
            [DayOfMonth::of(31), '31'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toStringProvider')]
    public function testToString(DayOfMonth $day, string $expected): void
    {
        self::assertEquals($expected, (string)$day);
    }

    public static function comparisonProvider(): array
    {
        return [
            [DayOfMonth::of(10), DayOfMonth::of(20), -1],
            [DayOfMonth::of(10), DayOfMonth::of(10), 0],
            [DayOfMonth::of(20), DayOfMonth::of(10), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(DayOfMonth $a, DayOfMonth $b, int $expected): void
    {
        self::assertTrue($a->isEqualTo($a));
        self::assertFalse($a->isNotEqualTo($a));
        self::assertFalse($a->isBefore($a));
        self::assertFalse($a->isAfter($a));
        self::assertTrue($a->isBeforeOrEqualTo($a));
        self::assertTrue($a->isAfterOrEqualTo($a));

        $comparison = $a->compareTo($b);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b));
        self::assertEquals($comparison->greater(), $a->isAfter($b));
        self::assertEquals($comparison->equal(), $a->isEqualTo($b));
        self::assertEquals($comparison->notEqual(), $a->isNotEqualTo($b));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b));
    }
}
