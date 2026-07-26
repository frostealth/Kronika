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
use Kronika\Duration;
use Kronika\Exception\FormatError;
use Kronika\Exception\MalformedString\DateTimeMalformedString;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Precision;
use Kronika\Time;
use Kronika\Time\Second;
use Kronika\Unit;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZonedDateTime::class)]
final class ZonedDateTimeTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    public static function ofProvider(): array
    {
        // EST only (not DST)
        return [
            [Date::of(2025, 3, 24), Time::midday(), new \DateTimeZone('-02:30')],
            [Date::of(1950, 2, 28), Time::endOfDay(), new \DateTimeZone('-05:00')],
            [Date::of(3000, 12, 10), Time::midnight(), new \DateTimeZone('-05:00')],
            [Date::of(2006, 4, 2), Time::of(1, 45, 24), new \DateTimeZone('America/New_York')],
            [Date::of(500, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
            [Date::of(9999, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
            [Date::of(99, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
            [Date::of(0, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
            [Date::of(-99, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
            [Date::of(-999, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
            [Date::of(-9999, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
        ];
    }

    #[DependsExternal(DateTest::class, 'testBasic')]
    #[DependsExternal(TimeTest::class, 'testBasic')]
    #[DependsOnClass(LocalDateTimeTest::class)]
    #[DataProvider('ofProvider')]
    public function testBasic(Date $date, Time $time, \DateTimeZone $timezone): void
    {
        // EST only (not DST)
        $datetime = ZonedDateTime::of(date: $date, time: $time, timezone: $timezone);

        self::assertSame($date, $datetime->date());
        self::assertSame($date->year(), $datetime->year());
        self::assertSame($date->month(), $datetime->month());
        self::assertSame($date->day(), $datetime->day());
        self::assertSame($date->dayOfWeek(), $datetime->dayOfWeek());
        self::assertSame($date->dayOfYear(), $datetime->dayOfYear());
        self::assertSame($time, $datetime->time());
        self::assertSame($time->hour(), $datetime->hour());
        self::assertSame($time->minute(), $datetime->minute());
        self::assertSame($time->second(), $datetime->second());
        self::assertEquals($time->second()->microsecond(), $datetime->microsecond());
        self::assertEquals($timezone->getName(), $datetime->timezone()->getName());
        self::assertEquals(\sprintf(
            '%s-%02d-%02d %02d:%02d:%02d.%06d %s',
            $date->year(),
            $date->month()->number(),
            $date->day()->number(),
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
            $timezone->getName(),
        ), $datetime->format('Y-m-d H:i:s.u e'));
        self::assertFalse($datetime->isDaylightSavingTime());
        self::assertSame(ZonedDateTime::of($date, $time, $timezone), $datetime);
    }

    #[Depends('testBasic')]
    public function testOfAndDst(): void
    {
        $datetime = ZonedDateTime::of(
            date: Date::of(2006, 4, 2),
            time: Time::of(2, 15, Second::of(45, 500_000)),
            timezone: new \DateTimeZone('America/New_York'),
        );

        self::assertEquals(2006, $datetime->year()->number());
        self::assertEquals(4, $datetime->month()->number());
        self::assertEquals(2, $datetime->day()->number());
        self::assertEquals(3, $datetime->hour()->value());
        self::assertEquals(15, $datetime->minute()->value());
        self::assertEquals(45, $datetime->second()->second());
        self::assertEquals(500_000, $datetime->second()->microsecond());
        self::assertEquals('America/New_York', $datetime->timezone()->getName());
        self::assertTrue($datetime->isDaylightSavingTime());
    }

    #[Depends('testBasic')]
    public function testShift(): void
    {
        $datetime = ZonedDateTime::of(
            date: Date::of(2025, 12, 31),
            time: Time::of(12, 15, 30),
            timezone: new \DateTimeZone('+01:00'),
        );

        $actual = $datetime->shift(new \DateTimeZone('+02:30'));

        self::assertEquals(new \DateTimeZone('+02:30'), $actual->timezone());
        self::assertEquals(Date::of(2025, 12, 31), $actual->date());
        self::assertEquals(Time::of(13, 45, 30), $actual->time());
    }

    #[TestWith(['Y-m-d', '2025-12-31'])]
    #[TestWith(['H:i:s.u', '12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59+01:00'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59+01:00'])]
    #[TestWith([
        '\D\a\t\e: "l, d M y", \T\i\m\e: "G \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Date: "Wednesday, 31 Dec 25", Time: "12 hours, 15 minutes, 59 seconds"',
    ])]
    #[Depends('testBasic')]
    public function testFormat(string $format, string $expected): void
    {
        $datetime = ZonedDateTime::of(
            date: Date::of(2025, 12, 31),
            time: Time::of(12, 15, Time\Second::last()),
            timezone: new \DateTimeZone('+01:00'),
        );

        $actual = $datetime->format($format);

        self::assertEquals($expected, $actual);
    }

    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59+01:00'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59+01:00'])]
    #[TestWith([
        '\D\a\t\e: "l, d M y", \T\i\m\e: "G \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Date: "Wednesday, 31 Dec 25", Time: "12 hours, 15 minutes, 59 seconds"',
    ])]
    #[Depends('testFormat')]
    public function testFromFormat(string $format, string $str): void
    {
        $datetime = ZonedDateTime::fromFormat($format, $str);

        self::assertEquals($str, $datetime->format($format));
    }

    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59'])]
    #[TestWith(['Y-m-d H:i:sP', '2025- 12:15:59+01:00'])]
    #[TestWith(['Y-m-d\TH:i:s.uO', ''])]
    #[TestWith(['', '2025-12-31T12:15:59+01:00'])]
    #[TestWith(['', ''])]
    #[Depends('testFromFormat')]
    public function testFromFormatFail(string $format, string $str): void
    {
        $this->expectException(FormatError::class);
        ZonedDateTime::fromFormat($format, $str);
    }

    #[TestWith(['2025-12-31T12:15:59.000999+01:30', [2025, 12, 31, 12, 15, 59, 999, '+01:30']])]
    #[TestWith(['2025-12-31T12:15:59.999999+00:00', [2025, 12, 31, 12, 15, 59, 999_999, '+00:00']])]
    #[TestWith(['2025-12-31 12:15:59.999999', [2025, 12, 31, 12, 15, 59, 999_999, 'Europe/Berlin']])]
    #[TestWith(['2025-12-31 12:15:59 UTC', [2025, 12, 31, 12, 15, 59, 0, 'UTC']])]
    #[TestWith(['2025-12-31 12:15 +0200', [2025, 12, 31, 12, 15, 0, 0, '+0200']])]
    #[TestWith(['12:15 31-12-2025 +01:00', [2025, 12, 31, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['15 Jan 25, 12:15:59 GMT', [2025, 1, 15, 12, 15, 59, 0, 'GMT']])]
    #[TestWith(['2006-04-02 02:15:59.000999 America/New_York', [2006, 4, 2, 3, 15, 59, 999, 'America/New_York']])]
    #[TestWith(['0099-11-30 12:15 +01:00', [99, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['9999-11-30 12:15 +01:00', [9999, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['0000-11-30 12:15 +01:00', [0, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['-0004-11-30 12:15 +01:00', [-4, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['-0032-11-30 12:15 +01:00', [-32, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['-0999-11-30 12:15 +01:00', [-999, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[TestWith(['-9999-11-30 12:15 +01:00', [-9999, 11, 30, 12, 15, 0, 0, '+01:00']])]
    #[Depends('testBasic')]
    public function testParse(string $str, array $expected): void
    {
        $expected = ZonedDateTime::of(
            date: Date::of($expected[0], $expected[1], $expected[2]),
            time: Time::of($expected[3], $expected[4], Second::of($expected[5], $expected[6])),
            timezone: new \DateTimeZone($expected[7]),
        );
        $actual = ZonedDateTime::parse($str);

        self::assertEquals($expected, $actual);
    }

    #[TestWith(['2025-12-31 12:15:98'])]
    #[TestWith(['2025-12-31 12:15:30 FAIL'])]
    #[TestWith(['2025 12:15'])]
    #[TestWith(['2025 12:15:59'])]
    #[TestWith(['2025-31-31 12:75:90'])]
    #[TestWith(['12/15 31 12 2025'])]
    #[TestWith(['15 Com 25, 12:15:59'])]
    #[TestWith([''])]
    #[TestWith(['now'])]
    #[TestWith(['Now'])]
    #[TestWith(['today'])]
    #[TestWith(['Today'])]
    #[Depends('testParse')]
    public function testParseFail(string $str): void
    {
        $this->expectException(DateTimeMalformedString::class);
        ZonedDateTime::parse($str);
    }

    public static function comparisonProvider(): array
    {
        return [
            // ZonedDateTime, Precision::Micro
            'Micro.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::EQUAL,
            ],
            'Micro.Microsecond.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Microsecond.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Month.Less' => [
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Month.Greater' => [
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Year.Less' => [
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Year.Greater' => [
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Epoch.Less' => [
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, 'UTC'),
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Epoch.Greater' => [
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, 'UTC'),
                self::zonedOf(1969, 12, 31, 23, 59, 59, 1, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Timezone.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 4545, '+01:00'),
                Precision::Micro,
                self::EQUAL,
            ],
            'Micro.Timezone.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 5555, '+01:00'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Timezone.Greater' => [
                self::zonedOf(2025, 10, 30, 13, 15, 45, 5555, '+01:00'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Native.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, '+01:00'),
                new \DateTime('2025-10-30 13:15:45.004545 +02:00'),
                Precision::Micro,
                self::EQUAL,
            ],
            'Micro.Native.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                new \DateTime('2025-10-30 13:15:55.004545 +02:00'),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Native.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                new \DateTime('2025-10-30 13:15:45.005555 +02:00'),
                Precision::Micro,
                self::GREATER,
            ],

            // ZonedDateTime, Precision::Second
            'Second.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Microsecond.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Microsecond.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'Second.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Month.Less' => [
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Month.Greater' => [
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Year.Less' => [
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Year.Greater' => [
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Timezone.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 4545, '+01:00'),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Timezone.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 55, 4545, '+01:00'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Timezone.Greater' => [
                self::zonedOf(2026, 10, 30, 13, 15, 55, 4545, '+01:00'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Native.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:45.004545 +02:00'),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Native.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:55.004545 +02:00'),
                Precision::Second,
                self::LESS,
            ],
            'Second.Native.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:45.005555 +02:00'),
                Precision::Second,
                self::GREATER,
            ],

            // ZonedDateTime, Precision::Minute
            'Minute.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Microsecond.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Microsecond.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Month.Less' => [
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Month.Greater' => [
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Year.Less' => [
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Year.Greater' => [
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Timezone.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 4545, '+01:00'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Timezone.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 20, 45, 4545, '+01:00'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Timezone.Greater' => [
                self::zonedOf(2026, 10, 30, 13, 20, 45, 4545, '+01:00'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Native.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:45.004545 +02:00'),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Native.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:20:45.004545 +02:00'),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Native.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:55.005555 +02:00'),
                Precision::Minute,
                self::GREATER,
            ],

            // LocalDateTime, Date and DateUnit
            'Date.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date::of(2025, 10, 30),
                Precision::Micro,
                self::EQUAL,
            ],
            'Date.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date::of(2025, 10, 31),
                Precision::Micro,
                self::LESS,
            ],
            'Date.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date::of(2025, 10, 29),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Year.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Year::of(2025),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Year.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Year::of(2026),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Year.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\Year::of(2024),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Month.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Month::of(10),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Month.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Month::of(11),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Month.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\Month::of(9),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.DayOfMonth.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(30),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(31),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\DayOfMonth::of(29),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.DayOfWeek.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfWeek::Thursday,
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.DayOfWeek.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfWeek::Friday,
                Precision::Micro,
                self::LESS,
            ],
            'Unit.DayOfWeek.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\DayOfWeek::Tuesday,
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.DayOfYear.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfYear::of(303),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.DayOfYear.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfYear::of(304),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.DayOfYear.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\DayOfYear::of(302),
                Precision::Micro,
                self::GREATER,
            ],

            // ZonedDateTime, Time and TimeUnit
            'Time.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time::of(12, 15, Second::of(55, 5555)),
                Precision::Micro,
                self::EQUAL,
            ],
            'Time.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time::of(12, 15, Second::of(55, 9999)),
                Precision::Micro,
                self::LESS,
            ],
            'Time.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, '+01:00'),
                Time::of(12, 15, Second::of(55)),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Hour.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Hour::of(12),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Hour::of(13),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Time\Hour::of(11),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Minute.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Minute::of(15),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Minute::of(20),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Time\Minute::of(10),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Second::of(55, 5555),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Second::of(55, 999999),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, '+01:00'),
                Time\Second::of(55),
                Precision::Micro,
                self::GREATER,
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(
        ZonedDateTime $a,
        \DateTimeInterface|Unit $b,
        Precision $precision,
        int $expected,
    ): void {
        self::assertFalse($a->isBefore($a, $precision));
        self::assertTrue($a->isBeforeOrEqualTo($a, $precision));
        self::assertTrue($a->is($a, $precision));
        self::assertFalse($a->isNot($a, $precision));
        self::assertTrue($a->isAfterOrEqualTo($a, $precision));
        self::assertFalse($a->isAfter($a, $precision));

        $comparison = $a->compareTo($b, $precision);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b, $precision));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b, $precision));
        self::assertEquals($comparison->greater(), $a->isAfter($b, $precision));
        self::assertEquals($comparison->equal(), $a->is($b, $precision));
        self::assertEquals($comparison->notEqual(), $a->isNot($b, $precision));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b, $precision));
        self::assertEquals($comparison->notEqual(), $a->isNot($b, $precision));
    }

    public static function untilProvider(): array
    {
        return [
            // ZonedDateTime
            'Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 50, 5555, '+01:00'),
                Duration::zero(),
            ],
            'Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                Duration::of(seconds: 4),
            ],
            'Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
            ],
            'Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+00:00'),
                Duration::of(hours: 1),
            ],
            'Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 16, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 1),
            ],
            'Date.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date::of(2025, 12, 20),
                Duration::of(days: 5),
            ],
            'Date.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date::of(2026, 1, 15),
                Duration::of(days: 31),
            ],
            'Unit.Year' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Year::of(2026),
                Duration::of(days: 365),
            ],
            'Unit.Month' => [
                self::zonedOf(2025, 11, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Month::December,
                Duration::of(days: 30),
            ],
            'Unit.DayOfMonth' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'Unit.DayOfWeek' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'Unit.DayOfYear' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfYear::of(350),
                Duration::of(days: 1),
            ],
            'Time.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'Time.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Time.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Time.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::zero(),
                Precision::Minute,
            ],
            'Unit.Hour' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'Unit.Hour.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(2),
                Duration::of(hours: 1),
            ],
            'Unit.Hour.DST.1' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(3),
                Duration::of(hours: 1),
            ],
            'Unit.Hour.DST.3' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(4),
                Duration::of(hours: 2),
            ],
            'Unit.Hour.DST.4' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(4),
                Duration::of(hours: 1),
            ],
            'Unit.Hour.DST.5' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(5),
                Duration::of(hours: 2),
            ],
            'Unit.Minute' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'Unit.Second.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'Unit.Second.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::of(seconds: 2),
                Precision::Second,
            ],
            'Unit.Second.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::zero(),
                Precision::Minute,
            ],

            // ZonedDateTime vs \DateTimeInterface
            'Native.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-15 12:45:50.005555 +01:00'),
                Duration::zero(),
            ],
            'Native.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.005555 +01:00'),
                Duration::of(seconds: 4),
            ],
            'Native.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.000000 +01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Native.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.000000 +01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Native.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.005555 +01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'Native.Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:50:55.005555 +01:00'),
                Duration::of(minutes: 5),
            ],
            'Native.Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-15 16:45:55.005555 +02:00'),
                Duration::of(hours: 3),
            ],
            'Native.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-16 12:45:55.005555 +01:00'),
                Duration::of(days: 1),
            ],
            'Native.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2026-02-15 14:45:55.005555 +03:00'),
                Duration::of(days: 62),
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('untilProvider')]
    public function testUntil(
        ZonedDateTime $datetime,
        \DateTimeInterface|Unit $end,
        Duration $expected,
        Precision $precision = Precision::Micro,
    ): void {
        $actual = $datetime->until($end, $precision);

        self::assertEquals($expected, $actual);
    }

    public static function differenceProvider(): array
    {
        return [
            // ZonedDateTime
            'Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::zero(),
            ],
            'Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(seconds: 4),
            ],
            'Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'Minutes.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
            ],
            'Minutes.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5, micros: 5555),
            ],
            'Minutes.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+00:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(hours: 1),
            ],
            'Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 16, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 1),
            ],
            'DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'DST.1' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'DST.2' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                Duration::of(hours: 2),
            ],
            'DST.3' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                Duration::of(hours: 2),
            ],
            'DST.4' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'Date.Days' => [
                self::zonedOf(2025, 12, 20, 12, 45, 55, 5555, '+01:00'),
                Date::of(2025, 12, 15),
                Duration::of(days: 5),
            ],
            'Date.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date::of(2026, 01, 15),
                Duration::of(days: 31),
            ],
            'Unit.Year' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Year::of(2026),
                Duration::of(days: 365),
            ],
            'Unit.Month' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Month::November,
                Duration::of(days: 30),
            ],
            'Unit.DayOfMonth' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'Unit.DayOfWeek' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'Unit.DayOfYear' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfYear::of(348),
                Duration::of(days: 1),
            ],
            'Time.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'Time.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Time.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Time.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::zero(),
                Precision::Minute,
            ],
            'Unit.Hour' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'Unit.Hour.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(2),
                Duration::ofHour(),
            ],
            'Unit.Hour.DST.1' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(3),
                Duration::ofHour(),
            ],
            'Unit.Hour.DST.2' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(4),
                Duration::of(hours: 2),
            ],
            'Unit.Hour.DST.3' => [
                self::zonedOf(2006, 4, 2, 4, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(1),
                Duration::of(hours: 2),
            ],
            'Unit.Hour.DST.4' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(5),
                Duration::of(hours: 2),
            ],
            'Unit.Minute' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'Unit.Second.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'Unit.Second.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::of(seconds: 1, micros: 994_445),
            ],
            'Unit.Second.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::of(seconds: 2),
                Precision::Second,
            ],

            // ZonedDateTime vs \DateTimeInterface
            'Native.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-15 12:45:55.005555 +01:00'),
                Duration::zero(),
            ],
            'Native.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.005555 +01:00'),
                Duration::of(seconds: 4),
            ],
            'Native.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.000000 +01:00'),
                Duration::of(seconds: 4, micros: 5555),
            ],
            'Native.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.000000 +01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Native.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.000000 +01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'Native.Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:50:55.005555 +01:00'),
                Duration::of(minutes: 5),
            ],
            'Native.Hours' => [
                self::zonedOf(2025, 12, 15, 16, 45, 55, 5555, '+02:00'),
                new \DateTime('2025-12-15 12:45:55.005555 +01:00'),
                Duration::of(hours: 3),
            ],
            'Native.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-16 12:45:55.005555 +01:00'),
                Duration::of(days: 1),
            ],
            'Native.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2026-02-15 14:45:55.005555 +03:00'),
                Duration::of(days: 62),
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('differenceProvider')]
    public function testDifference(
        ZonedDateTime $datetime,
        \DateTimeInterface|Unit $end,
        Duration $expected,
        Precision $precision = Precision::Micro,
    ): void {
        $actual = $datetime->difference($end, $precision);

        self::assertEquals($expected, $actual);
    }

    public static function addProvider(): array
    {
        return [
            'Duration.Zero' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::zero(),
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
            ],
            'Duration.Microseconds' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 500_000, '+01:00'),
                Duration::of(micros: 505_555),
                self::zonedOf(2025, 12, 30, 12, 15, 31, 5555, '+01:00'),
            ],
            'Duration.Seconds' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(seconds: 5, micros: 1),
                self::zonedOf(2025, 12, 30, 12, 15, 35, 5556, '+01:00'),
            ],
            'Duration.Minutes' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(minutes: 65),
                self::zonedOf(2025, 12, 30, 13, 20, 30, 5555, '+01:00'),
            ],
            'Duration.Hours' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(hours: 12, minutes: 30),
                self::zonedOf(2025, 12, 31, 0, 45, 30, 5555, '+01:00'),
            ],
            'Duration.Days' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::zonedOf(2026, 1, 1, 0, 45, 30, 5555, '+01:00'),
            ],
            'DateInterval' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::zonedOf(2026, 1, 1, 0, 45, 30, 5565, '+01:00'),
            ],
            'DST.Duration.Microseconds' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(micros: 500_000),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
            ],
            'DST.Duration.Hours.0' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(hours: 1),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.1' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(hours: 2),
                self::zonedOf(2006, 4, 2, 4, 59, 59, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.2' => [
                self::zonedOf(2006, 4, 1, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(hours: 26),
                self::zonedOf(2006, 4, 2, 4, 59, 59, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.3' => [
                self::zonedOf(2006, 4, 2, 0, 59, 59, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.4' => [
                self::zonedOf(2006, 4, 2, 3, 59, 59, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 4, 59, 59, 500_000, 'America/New_York'),
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('addProvider')]
    public function testAdd(ZonedDateTime $datetime, Duration|\DateInterval $duration, ZonedDateTime $expected): void
    {
        $actual = $datetime->add($duration);

        self::assertEquals($expected, $actual);
    }

    public static function subProvider(): array
    {
        return [
            'Duration.Zero' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::zero(),
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
            ],
            'Duration.Microseconds.0' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(micros: 6555),
                self::zonedOf(2025, 12, 30, 12, 15, 29, 999_000, '+01:00'),
            ],
            'Duration.Microseconds.1' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, '+01:00'),
                Duration::of(micros: 1),
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, '+01:00'),
            ],
            'Duration.Seconds' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(seconds: 5, micros: 1),
                self::zonedOf(2025, 12, 30, 12, 15, 25, 5554, '+01:00'),
            ],
            'Duration.Minutes' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(minutes: 65),
                self::zonedOf(2025, 12, 30, 11, 10, 30, 5555, '+01:00'),
            ],
            'Duration.Hours' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(hours: 12, minutes: 30),
                self::zonedOf(2025, 12, 29, 23, 45, 30, 5555, '+01:00'),
            ],
            'Duration.Days' => [
                self::zonedOf(2026, 1, 1, 12, 15, 30, 5555, '+01:00'),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::zonedOf(2025, 12, 30, 23, 45, 30, 5555, '+01:00'),
            ],
            'DateInterval' => [
                self::zonedOf(2026, 1, 1, 12, 15, 30, 5555, '+01:00'),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::zonedOf(2025, 12, 30, 23, 45, 30, 5545, '+01:00'),
            ],
            'DST.Duration.Microseconds' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 50_000, 'America/New_York'),
                Duration::of(micros: 550_000),
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.0' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.1' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 1, 0, 0, 500_000, 'America/New_York'),
            ],
            'DST.Duration.Hours.2' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 500_000, 'America/New_York'),
                Duration::of(hours: 2),
                self::zonedOf(2006, 4, 2, 1, 0, 0, 500_000, 'America/New_York'),
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('subProvider')]
    public function testSub(ZonedDateTime $datetime, Duration|\DateInterval $duration, ZonedDateTime $expected): void
    {
        $actual = $datetime->sub($duration);

        self::assertEquals($expected, $actual);
    }

    public static function instantProvider(): array
    {
        return [
            '0' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, '+00:00'),
                Instant::of(second: 0),
            ],
            '1' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 1, '+00:00'),
                Instant::of(second: 0, micro: 1),
            ],
            '2' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, '+01:00'),
                Instant::of(second: -3600),
            ],
            '3' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 1, '+01:00'),
                Instant::of(second: -3600, micro: 1),
            ],
            '4' => [
                self::zonedOf(1970, 1, 1, 0, 0, 1, 1, '+02:00'),
                Instant::of(second: -7199, micro: 1),
            ],
            '5' => [
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, '+01:00'),
                Instant::of(second: -3601, micro: 999_999),
            ],
            '6' => [
                self::zonedOf(1969, 12, 31, 23, 59, 58, 550_000, '+02:00'),
                Instant::of(second: -7202, micro: 550_000),
            ],
            'DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 15, 58, 550_000, 'America/New_York'),
                Instant::of(second: 1143958558, micro: 550_000),
            ],
            'DST.1' => [
                self::zonedOf(2006, 4, 2, 3, 15, 58, 550_000, 'America/New_York'),
                Instant::of(second: 1143962158, micro: 550_000),
            ],
            'DST.2' => [
                self::zonedOf(2006, 4, 2, 2, 15, 58, 550_000, 'America/New_York'),
                Instant::of(second: 1143962158, micro: 550_000),
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('instantProvider')]
    public function testInstant(ZonedDateTime $datetime, Instant $expected): void
    {
        self::assertEquals($expected, $datetime->instant());
    }

    #[Depends('testBasic')]
    public function testToLocalDateTime(): void
    {
        $local = LocalDateTime::of(Date::of(2025, 3, 24), Time::of(14, 8, 47));
        $zoned = ZonedDateTime::fromLocal($local, new \DateTimeZone('-02:30'));

        self::assertInstanceOf(LocalDateTime::class, $zoned->toLocalDateTime());
        self::assertSame($local, $zoned->toLocalDateTime());
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $datetime = ZonedDateTime::of(
            Date::of(2025, 3, 24),
            Time::endOfDay(),
            new \DateTimeZone('Europe/Berlin'),
        );

        self::assertEquals('2025-03-24 23:59:59.999999 Europe/Berlin', (string)$datetime);
    }

    #[Depends('testBasic')]
    public function testCreateFromInterface(): void
    {
        $datetime = ZonedDateTime::createFromInterface(new \DateTimeImmutable('2025-03-24T23:09:59.123456+01:30'));

        self::assertEquals('2025-03-24T23:09:59.123456+0130', $datetime->format('Y-m-d\TH:i:s.uO'));

        self::assertEquals(2025, $datetime->year()->number());
        self::assertEquals(3, $datetime->month()->number());
        self::assertEquals(24, $datetime->day()->number());

        self::assertEquals(23, $datetime->hour()->value());
        self::assertEquals(9, $datetime->minute()->value());
        self::assertEquals(59.123456, $datetime->second()->value());
        self::assertEquals(123456, $datetime->microsecond());
        self::assertEquals(123456, $datetime->getMicrosecond());

        self::assertEquals('+01:30', $datetime->timezone()->getName());
        self::assertEquals('+01:30', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromMutable(): void
    {
        $datetime = ZonedDateTime::createFromMutable(new \DateTime('2025-03-24T23:09:59.123456+01:30'));

        self::assertEquals('2025-03-24T23:09:59.123456+0130', $datetime->format('Y-m-d\TH:i:s.uO'));

        self::assertEquals(2025, $datetime->year()->number());
        self::assertEquals(3, $datetime->month()->number());
        self::assertEquals(24, $datetime->day()->number());

        self::assertEquals(23, $datetime->hour()->value());
        self::assertEquals(9, $datetime->minute()->value());
        self::assertEquals(59.123456, $datetime->second()->value());
        self::assertEquals(123456, $datetime->microsecond());
        self::assertEquals(123456, $datetime->getMicrosecond());

        self::assertEquals('+01:30', $datetime->timezone()->getName());
        self::assertEquals('+01:30', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromFormat(): void
    {
        $datetime = ZonedDateTime::createFromFormat(
            'Y-m-d\TH:i:s.uP',
            '2025-03-24T23:09:59.123456+01:30',
        );

        self::assertEquals('2025-03-24T23:09:59.123456+0130', $datetime->format('Y-m-d\TH:i:s.uO'));

        self::assertEquals(2025, $datetime->year()->number());
        self::assertEquals(3, $datetime->month()->number());
        self::assertEquals(24, $datetime->day()->number());

        self::assertEquals(23, $datetime->hour()->value());
        self::assertEquals(9, $datetime->minute()->value());
        self::assertEquals(59.123456, $datetime->second()->value());
        self::assertEquals(123456, $datetime->microsecond());
        self::assertEquals(123456, $datetime->getMicrosecond());

        self::assertEquals('+01:30', $datetime->timezone()->getName());
        self::assertEquals('+01:30', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromTimestamp(): void
    {
        $datetime = ZonedDateTime::createFromTimestamp(
            new \DateTimeImmutable('2025-03-24T23:09:59.123456+01:30')->getTimestamp(),
        );

        self::assertEquals('2025-03-24T21:39:59.000000+0000', $datetime->format('Y-m-d\TH:i:s.uO'));

        self::assertEquals(2025, $datetime->year()->number());
        self::assertEquals(3, $datetime->month()->number());
        self::assertEquals(24, $datetime->day()->number());

        self::assertEquals(21, $datetime->hour()->value());
        self::assertEquals(39, $datetime->minute()->value());
        self::assertEquals(59.0, $datetime->second()->value());
        self::assertEquals(0, $datetime->microsecond());
        self::assertEquals(0, $datetime->getMicrosecond());

        self::assertEquals('+00:00', $datetime->timezone()->getName());
        self::assertEquals('+00:00', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromNegativeTimestamp(): void
    {
        $datetime = ZonedDateTime::createFromTimestamp(-0.000001);

        self::assertEquals('1969-12-31T23:59:59.999999+0000', $datetime->format('Y-m-d\TH:i:s.uO'));

        self::assertEquals(1969, $datetime->year()->number());
        self::assertEquals(12, $datetime->month()->number());
        self::assertEquals(31, $datetime->day()->number());

        self::assertEquals(23, $datetime->hour()->value());
        self::assertEquals(59, $datetime->minute()->value());
        self::assertEquals(59.999999, $datetime->second()->value());
        self::assertEquals(999999, $datetime->microsecond());
        self::assertEquals(999999, $datetime->getMicrosecond());
        self::assertEquals(-0.000001, $datetime->timestamp());

        self::assertEquals('+00:00', $datetime->timezone()->getName());
        self::assertEquals('+00:00', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testNativeMethods(): void
    {
        $native = new \DateTimeImmutable('2025-03-24T23:09:59.123456+01:30');
        $kronika = ZonedDateTime::of(
            Date::of(2025, 3, 24),
            Time::of(23, 9, Time\Second::of(59, 123456)),
            new \DateTimeZone('+01:30'),
        );
        $format = 'Y-m-d\TH:i:s.uO';

        self::assertEquals($native->getTimestamp(), $kronika->getTimestamp());
        self::assertEquals($native->getTimezone()->getName(), $kronika->getTimezone()->getName());
        self::assertEquals($native->getOffset(), $kronika->getOffset());
        self::assertEquals(
            $native->setTime(9, 45, 32)->format($format),
            $kronika->setTime(9, 45, 32)->format($format),
        );
        self::assertEquals(
            $native->setDate(1982, 11, 24)->format($format),
            $kronika->setDate(1982, 11, 24)->format($format),
        );
        self::assertEquals(
            $native->setISODate(1500, 42, 3)->format($format),
            $kronika->setISODate(1500, 42, 3)->format($format),
        );
        self::assertEquals(
            $native->setTimezone(new \DateTimeZone('-01:30'))->format($format),
            $kronika->setTimezone(new \DateTimeZone('-01:30'))->format($format),
        );
        self::assertEquals(
            $native->setMicrosecond(999)->format($format),
            $kronika->setMicrosecond(999)->format($format),
        );
        self::assertEquals(
            $native->add(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->add(new \DateInterval('P3DT2H23M13S'))->format($format),
        );
        self::assertEquals(
            $native->add(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->add(Duration::of(days: 3, hours: 2, minutes: 23, seconds: 13))->format($format),
        );
        self::assertEquals(
            $native->sub(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->sub(new \DateInterval('P3DT2H23M13S'))->format($format),
        );
        self::assertEquals(
            $native->sub(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->sub(Duration::of(days: 3, hours: 2, minutes: 23, seconds: 13))->format($format),
        );
        self::assertEquals(
            $native->modify('+3 days')->format($format),
            $kronika->modify('+3 days')->format($format),
        );
        self::assertEquals(
            $native->diff(new \DateTime('2030-09-28 05:25:10 UTC'))->format($format),
            $kronika->diff(new \DateTime('2030-09-28 05:25:10 UTC'))->format($format),
        );
    }

    #[Depends('testBasic')]
    public function testToNative(): void
    {
        $native = new \DateTimeImmutable('2030-09-28 05:25:10.004582 +02:00');
        $kronika = ZonedDateTime::of(
            Date::of(2030, 9, 28),
            Time::of(5, 25, Time\Second::of(10, 4582)),
            new \DateTimeZone('+02:00'),
        );

        $result = $kronika->toNative();

        $format = 'Y-m-d\TH:i:s.uO';
        self::assertEquals($native->format($format), $result->format($format));
    }

    #[Depends('testBasic')]
    public function testToNativeMutable(): void
    {
        $native = new \DateTime('2030-09-28 05:25:10.004582 +02:00');
        $kronika = ZonedDateTime::of(
            Date::of(2030, 9, 28),
            Time::of(5, 25, Time\Second::of(10, 4582)),
            new \DateTimeZone('+02:00'),
        );

        $result = $kronika->toNativeMutable();

        $format = 'Y-m-d\TH:i:s.uO';
        self::assertEquals($native->format($format), $result->format($format));
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
        return ZonedDateTime::fromLocal(
            LocalDateTime::of(Date::of($year, $month, $day), Time::of($hour, $minute, Second::of($second, $micro))),
            new \DateTimeZone($timezone),
        );
    }
}
