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
use Kronika\Duration;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Precision;
use Kronika\Time;
use Kronika\Time\Second;
use Kronika\Unit;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClassesThatImplementInterface(DateTime::class)]
final class DateTimeTest extends TestCase
{
    public static function resetProvider(): array
    {
        return [
            [self::localOf(2025, 12, 15, 12, 45, 55, 5555)],
            [self::localOf(2025, 12, 15, 0, 30, 30, 0)],
            [self::localOf(2025, 12, 15, 0, 30, 0, 0)],
            [self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00')],
            [self::zonedOf(2025, 12, 15, 0, 30, 30, 0, '+01:00')],
            [self::zonedOf(2025, 12, 15, 0, 30, 0, 0, '+01:00')],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('resetProvider')]
    public function testResetMicro(DateTime $datetime): void
    {
        $result = $datetime->resetMicro();

        self::assertSame($datetime->date(), $result->date());
        self::assertSame($datetime->hour(), $result->hour());
        self::assertSame($datetime->minute(), $result->minute());
        self::assertEquals($datetime->second()->second(), $result->second()->second());
        self::assertEquals(0, $result->second()->microsecond());
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('resetProvider')]
    public function testResetSecond(DateTime $datetime): void
    {
        $result = $datetime->resetSecond();

        self::assertSame($datetime->date(), $result->date());
        self::assertSame($datetime->hour(), $result->hour());
        self::assertSame($datetime->minute(), $result->minute());
        self::assertEquals(0, $result->second()->second());
        self::assertEquals(0, $result->second()->microsecond());
    }

    public static function withProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.Date' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date::of(1834, 6, 25),
            ],
            'LocalDateTime.Year' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\Year::of(1999),
            ],
            'LocalDateTime.Month' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\Month::November,
            ],
            'LocalDateTime.DayOfMonth.14' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfMonth::of(14),
            ],
            'LocalDateTime.DayOfMonth.31' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfMonth::of(31),
            ],
            'LocalDateTime.DayOfWeek.Monday' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfWeek::Monday,
            ],
            'LocalDateTime.DayOfWeek.Wednesday' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfWeek::Wednesday,
            ],
            'LocalDateTime.DayOfWeek.Sunday' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfWeek::Sunday,
            ],
            'LocalDateTime.DayOfYear.First' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfYear::first(),
            ],
            'LocalDateTime.DayOfYear.Last.NonLeap' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfYear::last(),
            ],
            'LocalDateTime.DayOfYear.Last.Leap' => [
                self::localOf(2024, 3, 24, 23, 59, 59, 999999),
                Date\DayOfYear::last(),
            ],
            'LocalDateTime.Time' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time::midnight(),
            ],
            'LocalDateTime.Hour' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Hour::of(14),
            ],
            'LocalDateTime.Minute' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Minute::of(30),
            ],
            'LocalDateTime.Second.45' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Second::of(45),
            ],
            'LocalDateTime.Second.15.008765' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Second::of(15, 8765),
            ],

            // ZonedDateTime
            'ZonedDateTime.Date' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date::of(1834, 6, 25),
            ],
            'ZonedDateTime.Year' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\Year::of(1999),
            ],
            'ZonedDateTime.Month' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\Month::November,
            ],
            'ZonedDateTime.DayOfMonth.14' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfMonth::of(14),
            ],
            'ZonedDateTime.DayOfMonth.31' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfMonth::of(31),
            ],
            'ZonedDateTime.DayOfWeek.Monday' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfWeek::Monday,
            ],
            'ZonedDateTime.DayOfWeek.Wednesday' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfWeek::Wednesday,
            ],
            'ZonedDateTime.DayOfWeek.Sunday' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfWeek::Sunday,
            ],
            'ZonedDateTime.DayOfYear.First' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfYear::first(),
            ],
            'ZonedDateTime.DayOfYear.Last.NonLeap' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfYear::last(),
            ],
            'ZonedDateTime.DayOfYear.Last.Leap' => [
                self::zonedOf(2024, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfYear::last(),
            ],
            'ZonedDateTime.Time' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time::midnight(),
            ],
            'ZonedDateTime.Hour' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Hour::of(14),
            ],
            'ZonedDateTime.Minute' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Minute::of(30),
            ],
            'ZonedDateTime.Second.45' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Second::of(45),
            ],
            'ZonedDateTime.Second.15.008765' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Second::of(15, 8765),
            ],
            'ZonedDateTime.Timezone.UTC' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                new \DateTimeZone('UTC'),
            ],
            'ZonedDateTime.Timezone.+08:30' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                new \DateTimeZone('+08:30'),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(DateTime $datetime, Unit|\DateTimeZone $unit): void
    {
        $result = $datetime->with($unit);

         if ($unit instanceof Date) {
            self::assertSame($unit, $result->date());
            self::assertSame($datetime->time(), $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof Date\DateUnit) {
            self::assertSame($datetime->date()->with($unit), $result->date());
            self::assertSame($datetime->time(), $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof Time) {
            self::assertSame($datetime->date(), $result->date());
            self::assertSame($unit, $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof Time\TimeUnit) {
            self::assertSame($datetime->date(), $result->date());
            self::assertSame($datetime->time()->with($unit), $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof \DateTimeZone) {
            self::assertSame($datetime->date(), $result->date());
            self::assertSame($datetime->time(), $result->time());
            if ($result instanceof ZonedDateTime) {
                self::assertEquals($unit->getName(), $result->timezone()->getName());
            }
        }
    }

    public static function withAndDstProvider(): array
    {
        return [
            'Hour.0' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 999999, 'America/New_York'),
                Time\Hour::of(2),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'DayOfMonth.0' => [
                self::zonedOf(2006, 4, 1, 2, 59, 59, 999999, 'America/New_York'),
                Date\DayOfMonth::of(2),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Month.0' => [
                self::zonedOf(2006, 3, 2, 2, 59, 59, 999999, 'America/New_York'),
                Date\Month::of(4),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Year.0' => [
                self::zonedOf(2005, 4, 2, 2, 59, 59, 999999, 'America/New_York'),
                Date\Year::of(2006),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Date.0' => [
                self::zonedOf(2005, 11, 23, 2, 59, 59, 999999, 'America/New_York'),
                Date::of(2006, 4, 2),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Time.0' => [
                self::zonedOf(2006, 4, 2, 12, 59, 59, 999999, 'America/New_York'),
                Time::of(2, 12, 45),
                self::zonedOf(2006, 4, 2, 3, 12, 45, 0, 'America/New_York'),
            ],
        ];
    }

    #[Depends('testWith')]
    #[DataProvider('withAndDstProvider')]
    public function testWithAndDst(ZonedDateTime $datetime, Unit|\DateTimeZone $unit, ZonedDateTime $expected): void
    {
        self::assertEquals($expected, $datetime->with($unit));
    }

    private static function localOf(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        int $second,
        int $micro,
    ): LocalDateTime {
        return LocalDateTime::of(Date::of($year, $month, $day), Time::of($hour, $minute, Second::of($second, $micro)));
    }

    private static function zonedOf(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        int $second,
        int $micro,
        string $timezone,
    ): ZonedDateTime {
        return ZonedDateTime::ofLocal(
            self::localOf($year, $month, $day, $hour, $minute, $second, $micro),
            new \DateTimeZone($timezone),
        );
    }
}
