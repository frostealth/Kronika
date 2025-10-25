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
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Duration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class MonthTest extends TestCase
{
    public function testBasic(): void
    {
        $month = Month::of(1);

        $this->assertEquals(1, $month->number());
        $this->assertEquals('January', $month->name());
        $this->assertTrue($month->is(1));

        $this->assertNotSame($month, Month::of(2));
        $this->assertSame($month, Month::of($month));
        $this->assertSame($month, Month::of(1));

        $this->assertSame(Month::January, Month::of(1));
        $this->assertSame(Month::February, Month::of(2));
        $this->assertSame(Month::March, Month::of(3));
        $this->assertSame(Month::April, Month::of(4));
        $this->assertSame(Month::May, Month::of(5));
        $this->assertSame(Month::June, Month::of(6));
        $this->assertSame(Month::July, Month::of(7));
        $this->assertSame(Month::August, Month::of(8));
        $this->assertSame(Month::September, Month::of(9));
        $this->assertSame(Month::October, Month::of(10));
        $this->assertSame(Month::November, Month::of(11));
        $this->assertSame(Month::December, Month::of(12));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        Month::of(0);

        $this->expectException(\ValueError::class);
        Month::of(-1);

        $this->expectException(\ValueError::class);
        Month::of(13);
    }

    public static function comparisonProvider(): array
    {
        return [
            [Month::January, Month::February, -1],
            [Month::January, Month::January, 0],
            [Month::July, Month::June, 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Month $a, Month $b, int $expected): void
    {
        $this->assertTrue($a->isEqualTo($a));
        $this->assertFalse($a->isNotEqualTo($a));
        $this->assertFalse($a->isBefore($a));
        $this->assertFalse($a->isAfter($a));
        $this->assertTrue($a->isBeforeOrEqual($a));
        $this->assertTrue($a->isAfterOrEqual($a));

        $comparison = $a->compareTo($b);
        $this->assertEquals($expected, $comparison->value());
        $this->assertEquals($comparison->less(), $a->isBefore($b));
        $this->assertEquals($comparison->greater(), $a->isAfter($b));
        $this->assertEquals($comparison->equal(), $a->isEqualTo($b));
        $this->assertEquals($comparison->notEqual(), $a->isNotEqualTo($b));
        $this->assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqual($b));
        $this->assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqual($b));
    }

    public static function nextProvider(): array
    {
        return [
            [Month::January, Month::February],
            [Month::February, Month::March],
            [Month::March, Month::April],
            [Month::April, Month::May],
            [Month::May, Month::June],
            [Month::June, Month::July],
            [Month::July, Month::August],
            [Month::August, Month::September],
            [Month::September, Month::October],
            [Month::October, Month::November],
            [Month::November, Month::December],
            [Month::December, Month::January],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('nextProvider')]
    public function testNext(Month $current, Month $expected): void
    {
        $this->assertSame($expected, $current->next());
    }

    public static function previousProvider(): array
    {
        return [
            [Month::December, Month::November],
            [Month::November, Month::October],
            [Month::October, Month::September],
            [Month::September, Month::August],
            [Month::August, Month::July],
            [Month::July, Month::June],
            [Month::June, Month::May],
            [Month::May, Month::April],
            [Month::April, Month::March],
            [Month::March, Month::February],
            [Month::February, Month::January],
            [Month::January, Month::December],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('previousProvider')]
    public function testPrevious(Month $current, Month $expected): void
    {
        $this->assertSame($expected, $current->previous());
    }

    public static function lastDayProvider(): array
    {
        return [
            [Month::January, Year::of(2024), DayOfMonth::of(31)],
            [Month::January, Year::of(2025), DayOfMonth::of(31)],
            [Month::February, Year::of(2024), DayOfMonth::of(29)],
            [Month::February, Year::of(2025), DayOfMonth::of(28)],
            [Month::March, Year::of(2024), DayOfMonth::of(31)],
            [Month::March, Year::of(2025), DayOfMonth::of(31)],
            [Month::April, Year::of(2024), DayOfMonth::of(30)],
            [Month::April, Year::of(2025), DayOfMonth::of(30)],
            [Month::May, Year::of(2024), DayOfMonth::of(31)],
            [Month::May, Year::of(2025), DayOfMonth::of(31)],
            [Month::June, Year::of(2024), DayOfMonth::of(30)],
            [Month::June, Year::of(2025), DayOfMonth::of(30)],
            [Month::July, Year::of(2024), DayOfMonth::of(31)],
            [Month::July, Year::of(2025), DayOfMonth::of(31)],
            [Month::August, Year::of(2024), DayOfMonth::of(31)],
            [Month::August, Year::of(2025), DayOfMonth::of(31)],
            [Month::September, Year::of(2024), DayOfMonth::of(30)],
            [Month::September, Year::of(2025), DayOfMonth::of(30)],
            [Month::October, Year::of(2024), DayOfMonth::of(31)],
            [Month::October, Year::of(2025), DayOfMonth::of(31)],
            [Month::November, Year::of(2024), DayOfMonth::of(30)],
            [Month::November, Year::of(2025), DayOfMonth::of(30)],
            [Month::December, Year::of(2024), DayOfMonth::of(31)],
            [Month::December, Year::of(2025), DayOfMonth::of(31)],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('lastDayProvider')]
    public function testLastDay(Month $month, Year $year, DayOfMonth $expected): void
    {
        $this->assertEquals($expected, $month->lastDay($year));
        $this->assertEquals($expected->number(), $month->length($year));
        $this->assertEquals(Duration::of(days: $expected->number()), $month->duration($year));
    }

    public static function adjustDayProvider(): array
    {
        return [
            [Month::January, Year::of(2025), DayOfMonth::of(1), DayOfMonth::of(1)],
            [Month::January, Year::of(2025), DayOfMonth::of(15), DayOfMonth::of(15)],
            [Month::January, Year::of(2025), DayOfMonth::of(30), DayOfMonth::of(30)],
            [Month::January, Year::of(2025), DayOfMonth::of(31), DayOfMonth::of(31)],

            [Month::February, Year::of(2024), DayOfMonth::of(1), DayOfMonth::of(1)],
            [Month::February, Year::of(2024), DayOfMonth::of(15), DayOfMonth::of(15)],
            [Month::February, Year::of(2024), DayOfMonth::of(28), DayOfMonth::of(28)],
            [Month::February, Year::of(2024), DayOfMonth::of(29), DayOfMonth::of(29)],
            [Month::February, Year::of(2024), DayOfMonth::of(30), DayOfMonth::of(29)],
            [Month::February, Year::of(2024), DayOfMonth::of(31), DayOfMonth::of(29)],

            [Month::February, Year::of(2025), DayOfMonth::of(1), DayOfMonth::of(1)],
            [Month::February, Year::of(2025), DayOfMonth::of(15), DayOfMonth::of(15)],
            [Month::February, Year::of(2025), DayOfMonth::of(28), DayOfMonth::of(28)],
            [Month::February, Year::of(2025), DayOfMonth::of(29), DayOfMonth::of(28)],
            [Month::February, Year::of(2025), DayOfMonth::of(30), DayOfMonth::of(28)],
            [Month::February, Year::of(2025), DayOfMonth::of(31), DayOfMonth::of(28)],

            [Month::March, Year::of(2025), DayOfMonth::of(1), DayOfMonth::of(1)],
            [Month::March, Year::of(2025), DayOfMonth::of(15), DayOfMonth::of(15)],
            [Month::March, Year::of(2025), DayOfMonth::of(28), DayOfMonth::of(28)],
            [Month::March, Year::of(2025), DayOfMonth::of(29), DayOfMonth::of(29)],
            [Month::March, Year::of(2025), DayOfMonth::of(30), DayOfMonth::of(30)],
            [Month::March, Year::of(2025), DayOfMonth::of(31), DayOfMonth::of(31)],

            [Month::April, Year::of(2025), DayOfMonth::of(1), DayOfMonth::of(1)],
            [Month::April, Year::of(2025), DayOfMonth::of(15), DayOfMonth::of(15)],
            [Month::April, Year::of(2025), DayOfMonth::of(28), DayOfMonth::of(28)],
            [Month::April, Year::of(2025), DayOfMonth::of(29), DayOfMonth::of(29)],
            [Month::April, Year::of(2025), DayOfMonth::of(30), DayOfMonth::of(30)],
            [Month::April, Year::of(2025), DayOfMonth::of(31), DayOfMonth::of(30)],
        ];
    }

    #[Depends('testLastDay')]
    #[DataProvider('adjustDayProvider')]
    public function testAdjustDay(Month $month, Year $year, DayOfMonth $day, DayOfMonth $expected): void
    {
        $this->assertEquals($expected, $month->adjustDay($day, $year));
    }
}
