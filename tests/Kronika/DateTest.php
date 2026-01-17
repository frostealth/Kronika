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

namespace Kronika\Tests;

use Kronika\Date;
use Kronika\DateTime;
use Kronika\Exception\FormatError;
use Kronika\Exception\InvalidDate;
use Kronika\Exception\MalformedString\DateMalformedString;
use Kronika\LocalDateTime;
use Kronika\Tests\Date\DayOfMonthTest;
use Kronika\Tests\Date\DayOfWeekTest;
use Kronika\Tests\Date\DayOfYearTest;
use Kronika\Tests\Date\MonthTest;
use Kronika\Tests\Date\YearTest;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Date::class)]
final class DateTest extends TestCase
{
    public static function ofProvider(): array
    {
        return [
            [Date\Year::of(2025), Date\Month::of(1), Date\DayOfMonth::of(1)],
            [Date\Year::of(2025), Date\Month::of(1), Date\DayOfMonth::of(31)],
            [Date\Year::of(2025), Date\Month::February, Date\DayOfMonth::of(28)],
            [Date\Year::of(2025), Date\Month::of(6), Date\DayOfMonth::of(10)],
            [Date\Year::of(2025), Date\Month::of(6), Date\DayOfMonth::of(30)],
            [Date\Year::of(2025), Date\Month::of(10), Date\DayOfMonth::of(20)],
            [Date\Year::of(2025), Date\Month::of(12), Date\DayOfMonth::of(21)],
            [Date\Year::of(2025), Date\Month::of(12), Date\DayOfMonth::of(31)],
            [Date\Year::of(2030), Date\Month::of(12), Date\DayOfMonth::of(31)],
            [Date\Year::of(1961), Date\Month::of(5), Date\DayOfMonth::of(18)],
        ];
    }

    #[DependsOnClass(YearTest::class)]
    #[DependsOnClass(MonthTest::class)]
    #[DependsOnClass(DayOfMonthTest::class)]
    #[DependsOnClass(DayOfWeekTest::class)]
    #[DataProvider('ofProvider')]
    public function testBasic(Date\Year $year, Date\Month $month, Date\DayOfMonth $day): void
    {
        $date = Date::of(year: $year, month: $month, day: $day);

        self::assertEquals($year, $date->year());
        self::assertEquals($month, $date->month());
        self::assertEquals($day, $date->day());
        self::assertSame($date, Date::of(year: $year->number(), month: $month->number(), day: $day->number()));
        self::assertNotSame($date, Date::of(year: $year->number() + 1, month: $month->number(), day: $day->number()));
    }

    #[TestWith([100_000, 01, 01])]
    #[TestWith([-100_000, 01, 01])]
    #[TestWith([2025, 00, 01])]
    #[TestWith([2025, 13, 01])]
    #[TestWith([2025, 01, 00])]
    #[TestWith([2025, 01, 32])]
    #[TestWith([2024, 02, 30])]
    #[TestWith([2025, 02, 29])]
    #[TestWith([2025, 04, 31])]
    #[Depends('testBasic')]
    public function testInvalidValues(int $year, int $month, int $day): void
    {
        $this->expectException(InvalidDate::class);
        Date::of(year: $year, month: $month, day: $day);
    }

    public static function dayOfWeekProvider(): array
    {
        return [
            [Date::of(year: 2025, month: 12, day: 31), Date\DayOfWeek::Wednesday],
            [Date::of(year: 2025, month: 10, day: 30), Date\DayOfWeek::Thursday],
        ];
    }

    #[DependsOnClass(DayOfWeekTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('dayOfWeekProvider')]
    public function testDayOfWeek(Date $date, Date\DayOfWeek $expected): void
    {
        self::assertEquals($expected, $date->dayOfWeek());
        self::assertSame($expected, $date->dayOfWeek());
    }

    public static function dayOfYearProvider(): array
    {
        return [
            [Date::of(year: 2025, month: 1, day: 1), Date\DayOfYear::first()],
            [Date::of(year: 2025, month: 2, day: 1), Date\DayOfYear::of(32)],
            [Date::of(year: 2025, month: 3, day: 1), Date\DayOfYear::of(60)],
            [Date::of(year: 2025, month: 10, day: 30), Date\DayOfYear::of(303)],
            [Date::of(year: 2025, month: 12, day: 31), Date\DayOfYear::last(Date\Year::of(2025))],
            [Date::of(year: 2024, month: 1, day: 1), Date\DayOfYear::first()],
            [Date::of(year: 2024, month: 2, day: 1), Date\DayOfYear::of(32)],
            [Date::of(year: 2024, month: 2, day: 29), Date\DayOfYear::of(60)],
            [Date::of(year: 2024, month: 10, day: 30), Date\DayOfYear::of(304)],
            [Date::of(year: 2024, month: 12, day: 31), Date\DayOfYear::last(Date\Year::of(2024))],
        ];
    }

    #[DependsOnClass(DayOfYearTest::class)]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('dayOfYearProvider')]
    public function testDayOfYear(Date $date, Date\DayOfYear $expected): void
    {
        self::assertEquals($expected, $date->dayOfYear());
        self::assertSame($expected, $date->dayOfYear());
    }

    #[TestWith([2025, 12, 30])]
    #[TestWith([2000, 01, 30])]
    #[TestWith([2024, 10, 15])]
    #[Depends('testBasic')]
    public function testStartAndEndOfYear(int $year, int $month, int $day): void
    {
        $date = Date::of(year: $year, month: $month, day: $day);

        self::assertEquals(Date::of(year: $year, month: Date\Month::January, day: 1), $date->startOfYear());
        self::assertEquals(Date::of(year: $year, month: Date\Month::December, day: 31), $date->endOfYear());
    }

    public static function startAndEndOfMonthProvider(): array
    {
        return [
            [Date::of(2025, 1, 15), Date\DayOfMonth::of(31)],
            [Date::of(2025, 2, 19), Date\DayOfMonth::of(28)],
            [Date::of(2024, 2, 15), Date\DayOfMonth::of(29)],
            [Date::of(2025, 3, 10), Date\DayOfMonth::of(31)],
            [Date::of(2025, 3, 1), Date\DayOfMonth::of(31)],
            [Date::of(2025, 4, 15), Date\DayOfMonth::of(30)],
            [Date::of(2025, 5, 15), Date\DayOfMonth::of(31)],
            [Date::of(2025, 6, 15), Date\DayOfMonth::of(30)],
            [Date::of(2025, 7, 15), Date\DayOfMonth::of(31)],
            [Date::of(2025, 8, 15), Date\DayOfMonth::of(31)],
            [Date::of(2025, 9, 15), Date\DayOfMonth::of(30)],
            [Date::of(2025, 9, 30), Date\DayOfMonth::of(30)],
            [Date::of(2025, 10, 15), Date\DayOfMonth::of(31)],
            [Date::of(2025, 11, 15), Date\DayOfMonth::of(30)],
            [Date::of(2025, 1, 29), Date\DayOfMonth::of(31)],
            [Date::of(2025, 1, 31), Date\DayOfMonth::of(31)],
        ];
    }

    public static function withProvider(): array
    {
        return [
            [Date::of(2025, 1, 15), Date\Year::of(2000), Date::of(2000, 1, 15)],
            [Date::of(2024, 1, 12), Date\Year::of(2000), Date::of(2000, 1, 12)],
            [Date::of(2025, 1, 15), Date\Year::of(1249), Date::of(1249, 1, 15)],
            [Date::of(2025, 1, 15), Date\DayOfMonth::of(31), Date::of(2025, 1, 31)],
            [Date::of(2025, 1, 15), Date\DayOfMonth::of(10), Date::of(2025, 1, 10)],
            [Date::of(2025, 1, 12), Date\DayOfMonth::of(20), Date::of(2025, 1, 20)],
            [Date::of(2025, 2, 12), Date\DayOfMonth::of(28), Date::of(2025, 2, 28)],
            [Date::of(2025, 2, 12), Date\DayOfMonth::of(29), Date::of(2025, 2, 28)],
            [Date::of(2024, 2, 12), Date\DayOfMonth::of(29), Date::of(2024, 2, 29)],
            [Date::of(2024, 2, 12), Date\DayOfMonth::of(31), Date::of(2024, 2, 29)],
            [Date::of(2025, 3, 15), Date\DayOfMonth::of(30), Date::of(2025, 3, 30)],
            [Date::of(2025, 3, 15), Date\DayOfMonth::of(31), Date::of(2025, 3, 31)],
            [Date::of(2025, 11, 15), Date\DayOfMonth::of(17), Date::of(2025, 11, 17)],
            [Date::of(2025, 11, 15), Date\DayOfMonth::of(30), Date::of(2025, 11, 30)],
            [Date::of(2025, 11, 15), Date\DayOfMonth::of(31), Date::of(2025, 11, 30)],
            [Date::of(2025, 12, 15), Date\DayOfMonth::of(2), Date::of(2025, 12, 2)],
            [Date::of(2025, 12, 30), Date\DayOfMonth::of(30), Date::of(2025, 12, 30)],
            [Date::of(2025, 12, 31), Date\DayOfMonth::of(31), Date::of(2025, 12, 31)],
            [Date::of(2025, 1, 15), Date\DayOfWeek::of(1), Date::of(2025, 1, 13)],
            [Date::of(2025, 1, 15), Date\DayOfWeek::of(2), Date::of(2025, 1, 14)],
            [Date::of(2025, 1, 15), Date\DayOfWeek::of(5), Date::of(2025, 1, 17)],
            [Date::of(2025, 1, 15), Date\DayOfWeek::of(7), Date::of(2025, 1, 19)],
            [Date::of(2025, 2, 27), Date\DayOfWeek::of(1), Date::of(2025, 2, 24)],
            [Date::of(2025, 2, 27), Date\DayOfWeek::of(3), Date::of(2025, 2, 26)],
            [Date::of(2025, 2, 27), Date\DayOfWeek::of(5), Date::of(2025, 2, 28)],
            [Date::of(2025, 2, 27), Date\DayOfWeek::of(6), Date::of(2025, 3, 1)],
            [Date::of(2025, 2, 27), Date\DayOfWeek::of(7), Date::of(2025, 3, 2)],
            [Date::of(2025, 12, 29), Date\DayOfWeek::of(1), Date::of(2025, 12, 29)],
            [Date::of(2025, 12, 29), Date\DayOfWeek::of(3), Date::of(2025, 12, 31)],
            [Date::of(2025, 12, 29), Date\DayOfWeek::of(4), Date::of(2026, 1, 1)],
            [Date::of(2025, 12, 29), Date\DayOfWeek::of(7), Date::of(2026, 1, 4)],
            [Date::of(2025, 12, 29), Date\DayOfYear::first(), Date::of(2025, 1, 1)],
            [Date::of(2025, 12, 29), Date\DayOfYear::of(32), Date::of(2025, 2, 1)],
            [Date::of(2025, 12, 29), Date\DayOfYear::of(365), Date::of(2025, 12, 31)],
            [Date::of(2025, 12, 29), Date\DayOfYear::of(366), Date::of(2025, 12, 31)],
            [Date::of(2024, 12, 29), Date\DayOfYear::of(366), Date::of(2024, 12, 31)],
        ];
    }

    #[DependsOnClass(YearTest::class)]
    #[DependsOnClass(MonthTest::class)]
    #[DependsOnClass(DayOfMonthTest::class)]
    #[DependsOnClass(DayOfWeekTest::class)]
    #[DependsOnClass(DayOfYearTest::class)]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(Date $date, Date\DateUnit $unit, Date $expected): void
    {
        $result = $date->with($unit);

        self::assertEquals($expected, $result);
    }

    #[Depends('testBasic')]
    #[DataProvider('startAndEndOfMonthProvider')]
    public function testStartAndEndOfMonth(Date $date, Date\DayOfMonth $lastDayOfMonth): void
    {
        $startOfMonth = $date->startOfMonth();
        $endOfMonth = $date->endOfMonth();

        self::assertEquals(Date\DayOfMonth::of(1), $startOfMonth->day());
        $date->day() === Date\DayOfMonth::of(1)
            ? self::assertEquals($date, $startOfMonth)
            : self::assertNotEquals($date, $startOfMonth);

        self::assertEquals($lastDayOfMonth, $endOfMonth->day());
        $date->day() === $lastDayOfMonth
            ? self::assertEquals($date, $endOfMonth)
            : self::assertNotEquals($date, $endOfMonth);
    }

    #[TestWith([[2025, 12, 30], [2024, 12, 30]])]
    #[TestWith([[2000, 10, 31], [1999, 10, 31]])]
    #[TestWith([[2025, 2, 28], [2024, 2, 28]])]
    #[TestWith([[2024, 2, 29], [2023, 2, 28]])]
    #[Depends('testWith')]
    public function testPreviousYear(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->previousYear());
    }

    #[TestWith([[2025, 12, 30], [2026, 12, 30]])]
    #[TestWith([[1999, 10, 31], [2000, 10, 31]])]
    #[TestWith([[2025, 2, 28], [2026, 2, 28]])]
    #[TestWith([[2024, 2, 29], [2025, 2, 28]])]
    #[Depends('testWith')]
    public function testNextYear(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->nextYear());
    }

    #[TestWith([[2025, 12, 15], [2025, 11, 15]])]
    #[TestWith([[2025, 10, 31], [2025, 9, 30]])]
    #[TestWith([[2025, 6, 1], [2025, 5, 1]])]
    #[TestWith([[2025, 3, 31], [2025, 2, 28]])]
    #[TestWith([[2025, 3, 30], [2025, 2, 28]])]
    #[TestWith([[2025, 3, 29], [2025, 2, 28]])]
    #[TestWith([[2025, 1, 31], [2024, 12, 31]])]
    #[TestWith([[2025, 1, 30], [2024, 12, 30]])]
    #[TestWith([[2025, 1, 1], [2024, 12, 1]])]
    #[TestWith([[2024, 3, 31], [2024, 2, 29]])]
    #[TestWith([[2024, 3, 30], [2024, 2, 29]])]
    #[TestWith([[2024, 3, 29], [2024, 2, 29]])]
    #[DependsOnClass(YearTest::class)]
    #[DependsOnClass(MonthTest::class)]
    #[DependsOnClass(DayOfMonthTest::class)]
    #[Depends('testWith')]
    public function testToPreviousMonth(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->previousMonth());
    }

    #[TestWith([[2024, 12, 15], [2025, 1, 15]])]
    #[TestWith([[2024, 12, 31], [2025, 1, 31]])]
    #[TestWith([[2025, 11, 15], [2025, 12, 15]])]
    #[TestWith([[2025, 9, 30], [2025, 10, 30]])]
    #[TestWith([[2025, 5, 1], [2025, 6, 1]])]
    #[TestWith([[2025, 7, 31], [2025, 8, 31]])]
    #[TestWith([[2025, 1, 29], [2025, 2, 28]])]
    #[TestWith([[2025, 1, 31], [2025, 2, 28]])]
    #[TestWith([[2025, 2, 28], [2025, 3, 28]])]
    #[TestWith([[2025, 1, 1], [2025, 2, 1]])]
    #[TestWith([[2024, 1, 31], [2024, 2, 29]])]
    #[TestWith([[2024, 1, 30], [2024, 2, 29]])]
    #[TestWith([[2024, 1, 29], [2024, 2, 29]])]
    #[DependsOnClass(YearTest::class)]
    #[DependsOnClass(MonthTest::class)]
    #[DependsOnClass(DayOfMonthTest::class)]
    #[Depends('testWith')]
    public function testToNextMonth(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->nextMonth());
    }

    #[TestWith([[2025, 12, 31], [2025, 12, 24]])]
    #[TestWith([[2025, 10, 6], [2025, 9, 29]])]
    #[TestWith([[2025, 9, 5], [2025, 8, 29]])]
    #[TestWith([[2025, 3, 5], [2025, 2, 26]])]
    #[TestWith([[2025, 1, 4], [2024, 12, 28]])]
    #[TestWith([[2024, 3, 4], [2024, 2, 26]])]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    public function testToPreviousWeek(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->previousWeek());
    }

    #[TestWith([[2024, 1, 15], [2024, 1, 22]])]
    #[TestWith([[2024, 1, 28], [2024, 2, 4]])]
    #[TestWith([[2024, 2, 21], [2024, 2, 28]])]
    #[TestWith([[2024, 2, 22], [2024, 2, 29]])]
    #[TestWith([[2024, 2, 23], [2024, 3, 1]])]
    #[TestWith([[2024, 5, 25], [2024, 6, 1]])]
    #[TestWith([[2024, 7, 24], [2024, 7, 31]])]
    #[TestWith([[2024, 12, 24], [2024, 12, 31]])]
    #[TestWith([[2024, 12, 27], [2025, 1, 3]])]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    public function testToNextWeek(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->nextWeek());
    }

    #[TestWith([[2025, 1, 15], [2025, 1, 14]])]
    #[TestWith([[2025, 5, 30], [2025, 5, 29]])]
    #[TestWith([[2025, 6, 1], [2025, 5, 31]])]
    #[TestWith([[2025, 3, 1], [2025, 2, 28]])]
    #[TestWith([[2025, 12, 31], [2025, 12, 30]])]
    #[TestWith([[2025, 1, 1], [2024, 12, 31]])]
    #[TestWith([[2024, 3, 1], [2024, 2, 29]])]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    public function testToYesterday(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->previousDay());
    }

    #[TestWith([[2024, 12, 30], [2024, 12, 31]])]
    #[TestWith([[2024, 12, 31], [2025, 1, 1]])]
    #[TestWith([[2025, 1, 1], [2025, 1, 2]])]
    #[TestWith([[2025, 1, 31], [2025, 2, 1]])]
    #[TestWith([[2025, 2, 27], [2025, 2, 28]])]
    #[TestWith([[2025, 2, 28], [2025, 3, 1]])]
    #[TestWith([[2025, 3, 30], [2025, 3, 31]])]
    #[TestWith([[2025, 10, 14], [2025, 10, 15]])]
    #[TestWith([[2024, 2, 28], [2024, 2, 29]])]
    #[TestWith([[2024, 2, 29], [2024, 3, 1]])]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    public function testToTomorrow(array $date, array $expected): void
    {
        $date = Date::of(...$date);
        $expected = Date::of(...$expected);

        self::assertEquals($expected, $date->nextDay());
    }

    #[TestWith(['Y-m-d', '2025-01-15'])]
    #[TestWith(['Y m d', '2025 01 15'])]
    #[TestWith(['Y-n-d', '2025-1-15'])]
    #[TestWith(['d/m/Y', '15/01/2025'])]
    #[TestWith(['H:i:s.u', 'H:i:s.u'])]
    #[TestWith(['w', '3'])]
    #[TestWith(['N', '3'])]
    #[TestWith(['L', '0'])]
    #[TestWith(['\Y/m/d', 'Y/01/15'])]
    #[TestWith(['\D\a\t\e: "l, d M y"', 'Date: "Wednesday, 15 Jan 25"'])]
    #[Depends('testBasic')]
    public function testFormat(string $format, string $expected): void
    {
        $date = Date::of(2025, 01, 15);

        self::assertEquals($expected, $date->format($format));
    }

    #[TestWith(['Y-m-d', '2025-01-15'])]
    #[TestWith(['Y m d', '2025 01 15'])]
    #[TestWith(['Y-n-d', '2025-1-15'])]
    #[TestWith(['d/m/Y', '15/01/2025'])]
    #[TestWith(['H:i:s.u', 'H:i:s.u'])]
    #[TestWith(['\Y/m/d', 'Y/01/15'])]
    #[TestWith(['\D\a\t\e: "l, d M y"', 'Date: "Wednesday, 15 Jan 25"'])]
    #[Depends('testFormat')]
    public function testOfFormat(string $format, string $str): void
    {
        $date = Date::ofFormat($format, $str);

        self::assertEquals($str, $date->format($format));
    }

    #[TestWith(['Y-m-d', '2025-01'])]
    #[TestWith(['H:i:s', '12:00:10'])]
    #[TestWith(['H:i:s', ''])]
    #[TestWith(['', '2025-01-31'])]
    #[TestWith(['', ''])]
    #[Depends('testOfFormat')]
    public function testOfFormatFail(string $format, string $str): void
    {
        $this->expectException(FormatError::class);
        Date::ofFormat($format, $str);
    }

    #[TestWith(['2025-01-15', [2025, 01, 15]])]
    #[TestWith(['1980-12-05', [1980, 12, 05]])]
    #[TestWith(['15 Jan 25', [2025, 01, 15]])]
    #[TestWith(['15-12-2025', [2025, 12, 15]])]
    #[Depends('testBasic')]
    public function testParse(string $str, array $expected): void
    {
        self::assertEquals(Date::of(...$expected), Date::parse($str));
    }

    #[TestWith(['1980 12 05'])]
    #[TestWith(['15 15 2025'])]
    #[TestWith(['15-15-2025'])]
    #[TestWith([''])]
    #[TestWith(['now'])]
    #[TestWith(['Now'])]
    #[TestWith(['today'])]
    #[TestWith(['Today'])]
    #[Depends('testParse')]
    public function testParseFail(string $str): void
    {
        self::expectException(DateMalformedString::class);
        Date::parse($str);
    }

    public static function ofDateTimeProvider(): array
    {
        return [
            [new \DateTime('1985-04-28 12:46:12.123456 UTC'), Date::of(year: 1985, month: 4, day: 28)],
            [new \DateTime('1985-05-21 12:46:12 +02:00'), Date::of(year: 1985, month: 5, day: 21)],
            [new \DateTime('2020-07-01'), Date::of(year: 2020, month: 7, day: 1)],
            [new \DateTime('2024-02-29'), Date::of(year: 2024, month: 2, day: 29)],
            [new \DateTimeImmutable('1985-04-28 12:46:12.123456 UTC'), Date::of(year: 1985, month: 4, day: 28)],
            [new \DateTimeImmutable('1985-05-21 12:46:12 +02:00'), Date::of(year: 1985, month: 5, day: 21)],
            [new \DateTimeImmutable('2020-07-01'), Date::of(year: 2020, month: 7, day: 1)],
            [new \DateTimeImmutable('2024-02-29'), Date::of(year: 2024, month: 2, day: 29)],
            [
                LocalDateTime::of(Date::of(1985, 4, 28), Time::of(12, 46, 12)),
                Date::of(year: 1985, month: 4, day: 28),
            ],
            [
                ZonedDateTime::of(
                    Date::of(1985, 4, 28),
                    Time::of(12, 46, 12),
                    new \DateTimeZone('+10:00'),
                ),
                Date::of(year: 1985, month: 4, day: 28),
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('ofDateTimeProvider')]
    public function testOfDateTime(DateTime|\DateTimeInterface $input, Date $expected): void
    {
        $actual = Date::ofDateTime($input);

        self::assertEquals($expected, $actual);
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $date = Date::of(2030, 5, 24);

        self::assertEquals('2030-05-24', (string)$date);
    }
}
