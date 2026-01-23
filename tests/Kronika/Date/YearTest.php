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

use Kronika\Date\Exception\InvalidYear;
use Kronika\Date\Year;
use Kronika\Duration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Year::class)]
final class YearTest extends TestCase
{
    #[TestWith([2025, '2025'])]
    #[TestWith([3521, '3521'])]
    #[TestWith([25, '0025'])]
    #[TestWith([7, '0007'])]
    #[TestWith([0, '0000'])]
    #[TestWith([-3, '-0003'])]
    #[TestWith([-999, '-0999'])]
    #[TestWith([-9999, '-9999'])]
    public function testBasic(int $number, string $str): void
    {
        $year = Year::of($number);

        self::assertEquals($number, $year->number());
        self::assertEquals($str, (string)$year);
        self::assertNotSame($year, Year::of(\abs($number) - 1));
        self::assertSame($year, Year::of($year));
        self::assertSame($year, Year::of($number));
    }

    #[TestWith([10_000])]
    #[TestWith([-10_000])]
    #[Depends('testBasic')]
    public function testInvalidValue(int $value): void
    {
        $this->expectException(InvalidYear::class);
        Year::of($value);
    }

    public static function comparisonProvider(): array
    {
        return [
            [Year::of(2025), Year::of(2026), -1],
            [Year::of(2025), Year::of(2025), 0],
            [Year::of(2026), Year::of(2025), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Year $a, Year $b, int $expected): void
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

    #[Depends('testComparison')]
    public function testPrevious(): void
    {
        $current = Year::of(2025);
        $previous = Year::of(2024);

        $result = $current->previous();

        self::assertEquals($previous->number(), $result->number());
        self::assertNotSame($current, $result);
        self::assertSame($previous, $result);
    }

    #[Depends('testComparison')]
    public function testNext(): void
    {
        $current = Year::of(2025);
        $next = Year::of(2026);

        $result = $current->next();

        self::assertEquals($next->number(), $result->number());
        self::assertNotSame($current, $result);
        self::assertSame($next, $result);
    }

    public static function leapProvider(): array
    {
        return [
            [2025, false],
            [2024, true],
            [1900, false],
            [2000, true],
            [2050, false],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('leapProvider')]
    public function testLeap(int $value, bool $expected): void
    {
        $year = Year::of($value);

        self::assertEquals($expected, $year->isLeap());
        self::assertEquals($expected ? 366 : 365, $year->length());
        self::assertEquals(Duration::of(days: $expected ? 366 : 365), $year->duration());
    }
}
