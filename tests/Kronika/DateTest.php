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
use Kronika\LocalDateTime;
use Kronika\Tests\Date\DayOfMonthTest;
use Kronika\Tests\Date\DayOfWeekTest;
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
            [Date\Year::of(2025), Date\Month::of(1), Date\DayOfMonth::of(1), Date\DayOfWeek::Wednesday],
            [Date\Year::of(2025), Date\Month::of(1), Date\DayOfMonth::of(31), Date\DayOfWeek::of(5)],
            [Date\Year::of(2025), Date\Month::February, Date\DayOfMonth::of(28), Date\DayOfWeek::of(5)],
            [Date\Year::of(2025), Date\Month::of(6), Date\DayOfMonth::of(10), Date\DayOfWeek::of(2)],
            [Date\Year::of(2025), Date\Month::of(6), Date\DayOfMonth::of(30), Date\DayOfWeek::of(1)],
            [Date\Year::of(2025), Date\Month::of(10), Date\DayOfMonth::of(20), Date\DayOfWeek::of(1)],
            [Date\Year::of(2025), Date\Month::of(12), Date\DayOfMonth::of(21), Date\DayOfWeek::of(7)],
            [Date\Year::of(2025), Date\Month::of(12), Date\DayOfMonth::of(31), Date\DayOfWeek::of(3)],
            [Date\Year::of(2030), Date\Month::of(12), Date\DayOfMonth::of(31), Date\DayOfWeek::of(2)],
            [Date\Year::of(1961), Date\Month::of(5), Date\DayOfMonth::of(18), Date\DayOfWeek::of(4)],
        ];
    }

    #[DependsOnClass(YearTest::class)]
    #[DependsOnClass(MonthTest::class)]
    #[DependsOnClass(DayOfMonthTest::class)]
    #[DependsOnClass(DayOfWeekTest::class)]
    #[DataProvider('ofProvider')]
    public function testBasic(Date\Year $year, Date\Month $month, Date\DayOfMonth $day, Date\DayOfWeek $dayOfWeek): void
    {
        $date = Date::of(year: $year, month: $month, day: $day);

        self::assertEquals($year, $date->year());
        self::assertEquals($month, $date->month());
        self::assertEquals($day, $date->day());
        self::assertEquals($dayOfWeek, $date->dayOfWeek());
        self::assertSame($date, Date::of(year: $year->number(), month: $month->number(), day: $day->number()));
        self::assertNotSame($date, Date::of(year: $year->number() + 1, month: $month->number(), day: $day->number()));
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

    #[Depends('testBasic')]
    #[DataProvider('startAndEndOfMonthProvider')]
    public function testStartAndEndOfMonth(Date $date, Date\DayOfMonth $lastDayOfMonth): void
    {
        $startOfMonth = $date->toStartOfMonth();
        $endOfMonth = $date->toEndOfMonth();

        self::assertEquals(Date\DayOfMonth::of(1), $startOfMonth->day());
        $date->day() === Date\DayOfMonth::of(1)
            ? self::assertEquals($date, $startOfMonth)
            : self::assertNotEquals($date, $startOfMonth);

        self::assertEquals($lastDayOfMonth, $endOfMonth->day());
        $date->day() === $lastDayOfMonth
            ? self::assertEquals($date, $endOfMonth)
            : self::assertNotEquals($date, $endOfMonth);
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(Date $date, Date\DateUnit $unit, Date $expected): void
    {
        $result = $date->with($unit);

        self::assertEquals($expected, $result);
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

    public function testToString(): void
    {
        $date = Date::of(2030, 5, 24);

        self::assertEquals('2030-05-24', (string) $date);
    }
}
