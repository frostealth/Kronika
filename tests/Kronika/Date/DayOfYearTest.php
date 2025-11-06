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

use Kronika\Date\DayOfYear;
use Kronika\Date\Exception\InvalidDayOfYear;
use Kronika\Date\Year;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(DayOfYear::class)]
final class DayOfYearTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    #[TestWith([1])]
    #[TestWith([10])]
    #[TestWith([100])]
    #[TestWith([200])]
    #[TestWith([300])]
    #[TestWith([365])]
    #[TestWith([366])]
    public function testBasic(int $number): void
    {
        $day = DayOfYear::of($number);

        self::assertEquals($number, $day->number());
        self::assertSame($day, DayOfYear::of($day));
        self::assertSame($day, DayOfYear::of($number));
        self::assertNotSame($day, DayOfYear::of($number < 365 ? $number + 1 : 1));
    }

    #[TestWith([0])]
    #[TestWith([367])]
    #[TestWith([-1])]
    #[TestWith([-366])]
    #[Depends('testBasic')]
    public function testInvalidNumber(int $number): void
    {
        $this->expectException(InvalidDayOfYear::class);
        DayOfYear::of($number);
    }

    #[Depends('testBasic')]
    public function testFirst(): void
    {
        $expected = DayOfYear::of(1);
        $actual = DayOfYear::first();

        self::assertEquals($expected->number(), $actual->number());
        self::assertSame($expected, $actual);
    }

    #[TestWith([null, 366])]
    #[TestWith([2024, 366])]
    #[TestWith([2025, 365])]
    #[DependsOnClass(YearTest::class)]
    #[Depends('testBasic')]
    public function testLast(?int $year, int $expected): void
    {
        $year = \is_null($year) ? null : Year::of($year);
        $expected = DayOfYear::of($expected);
        $actual = DayOfYear::last($year);

        self::assertEquals($expected->number(), $actual->number());
        self::assertSame($expected, $actual);
    }

    public static function nextProvider(): array
    {
        return [
            [DayOfYear::of(1), DayOfYear::of(2)],
            [DayOfYear::of(60), DayOfYear::of(61)],
            [DayOfYear::of(300), DayOfYear::of(301)],
            [DayOfYear::of(364), DayOfYear::of(365)],
            [DayOfYear::of(365), DayOfYear::of(366)],
            [DayOfYear::of(366), DayOfYear::of(366)],
            [DayOfYear::of(364), DayOfYear::of(365), null, true],
            [DayOfYear::of(365), DayOfYear::of(366), null, true],
            [DayOfYear::of(366), DayOfYear::of(1), null, true],
            [DayOfYear::of(364), DayOfYear::of(365), Year::of(2024)],
            [DayOfYear::of(365), DayOfYear::of(366), Year::of(2024)],
            [DayOfYear::of(366), DayOfYear::of(366), Year::of(2024)],
            [DayOfYear::of(364), DayOfYear::of(365), Year::of(2024), true],
            [DayOfYear::of(365), DayOfYear::of(366), Year::of(2024), true],
            [DayOfYear::of(366), DayOfYear::of(1), Year::of(2024), true],
            [DayOfYear::of(364), DayOfYear::of(365), Year::of(2025), true],
            [DayOfYear::of(365), DayOfYear::of(1), Year::of(2025), true],
            [DayOfYear::of(366), DayOfYear::of(1), Year::of(2025), true],
        ];
    }

    #[DependsOnClass(YearTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('nextProvider')]
    public function testNext(DayOfYear $day, DayOfYear $expected, ?Year $year = null, bool $rolling = false): void
    {
        self::assertEquals($expected, $day->next($year, $rolling));
        self::assertSame($expected, $day->next($year, $rolling));
    }

    public static function previousProvider(): array
    {
        return [
            [DayOfYear::of(1), DayOfYear::of(1)],
            [DayOfYear::of(2), DayOfYear::of(1)],
            [DayOfYear::of(60), DayOfYear::of(59)],
            [DayOfYear::of(300), DayOfYear::of(299)],
            [DayOfYear::of(364), DayOfYear::of(363)],
            [DayOfYear::of(365), DayOfYear::of(364)],
            [DayOfYear::of(366), DayOfYear::of(365)],
            [DayOfYear::of(364), DayOfYear::of(363), null, true],
            [DayOfYear::of(365), DayOfYear::of(364), null, true],
            [DayOfYear::of(366), DayOfYear::of(365), null, true],
            [DayOfYear::of(1), DayOfYear::of(1), Year::of(2024)],
            [DayOfYear::of(2), DayOfYear::of(1), Year::of(2024), true],
            [DayOfYear::of(1), DayOfYear::of(365), Year::of(2024), true],
            [DayOfYear::of(1), DayOfYear::of(1), Year::of(2025)],
            [DayOfYear::of(2), DayOfYear::of(1), Year::of(2025), true],
            [DayOfYear::of(1), DayOfYear::of(366), Year::of(2025), true],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2024)],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2024)],
            [DayOfYear::of(366), DayOfYear::of(365), Year::of(2024)],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2025)],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2025)],
            [DayOfYear::of(366), DayOfYear::of(364), Year::of(2025)],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2024)],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2024)],
            [DayOfYear::of(366), DayOfYear::of(365), Year::of(2024)],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2025)],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2025)],
            [DayOfYear::of(366), DayOfYear::of(364), Year::of(2025)],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2024), true],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2024), true],
            [DayOfYear::of(366), DayOfYear::of(365), Year::of(2024), true],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2025), true],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2025), true],
            [DayOfYear::of(366), DayOfYear::of(364), Year::of(2025), true],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2024), true],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2024), true],
            [DayOfYear::of(366), DayOfYear::of(365), Year::of(2024), true],
            [DayOfYear::of(364), DayOfYear::of(363), Year::of(2025), true],
            [DayOfYear::of(365), DayOfYear::of(364), Year::of(2025), true],
            [DayOfYear::of(366), DayOfYear::of(364), Year::of(2025), true],
        ];
    }

    #[DependsOnClass(YearTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('previousProvider')]
    public function testPrevious(DayOfYear $day, DayOfYear $expected, ?Year $year = null, bool $rolling = false): void
    {
        self::assertEquals($expected, $day->previous($year, $rolling));
        self::assertSame($expected, $day->previous($year, $rolling));
    }

    public static function comparisonProvider(): array
    {
        return [
            [DayOfYear::of(1), DayOfYear::of(1), self::EQUAL],
            [DayOfYear::of(1), DayOfYear::of(2), self::LESS],
            [DayOfYear::of(2), DayOfYear::of(1), self::GREATER],
            [DayOfYear::of(60), DayOfYear::of(60), self::EQUAL],
            [DayOfYear::of(60), DayOfYear::of(61), self::LESS],
            [DayOfYear::of(61), DayOfYear::of(60), self::GREATER],
            [DayOfYear::of(364), DayOfYear::of(364), self::EQUAL],
            [DayOfYear::of(364), DayOfYear::of(365), self::LESS],
            [DayOfYear::of(365), DayOfYear::of(364), self::GREATER],
            [DayOfYear::of(365), DayOfYear::of(365), self::EQUAL],
            [DayOfYear::of(365), DayOfYear::of(366), self::LESS],
            [DayOfYear::of(366), DayOfYear::of(365), self::GREATER],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(DayOfYear $a, DayOfYear $b, int $expected): void
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

    #[TestWith([1])]
    #[TestWith([10])]
    #[TestWith([300])]
    #[TestWith([365])]
    #[Depends('testBasic')]
    public function testToString(int $number): void
    {
        $day = DayOfYear::of($number);

        self::assertEquals((string)$number, (string)$day);
    }
}
