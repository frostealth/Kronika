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
use Kronika\Exception\MalformedString\DateTimeMalformedString;
use Kronika\Format\Exception\FormatterError;
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

#[CoversClass(LocalDateTime::class)]
final class LocalDateTimeTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    public static function ofProvider(): array
    {
        return [
            [Date::of(2025, 3, 24), Time::midday()],
            [Date::of(1950, 2, 28), Time::endOfDay()],
            [Date::of(1970, 1, 1), Time::midnight()],
            [Date::of(1969, 1, 1), Time::midnight()],
            [Date::of(1969, 12, 31), Time::endOfDay()],
            [Date::of(3000, 12, 10), Time::midnight()],
            [Date::of(5000, 12, 31), Time::endOfDay()],
            [Date::of(500, 1, 15), Time::of(9, 45, 24)],
            [Date::of(2950, 11, 21), Time::of(23, 45, Time\Second::of(35, 4455))],
            [Date::of(0, 1, 15), Time::of(9, 45, 24)],
            [Date::of(9999, 1, 15), Time::of(9, 45, 24)],
            [Date::of(-99, 1, 15), Time::of(9, 45, 24)],
            [Date::of(-9999, 1, 15), Time::of(9, 45, 24)],
        ];
    }

    #[DependsExternal(DateTest::class, 'testBasic')]
    #[DependsExternal(TimeTest::class, 'testBasic')]
    #[DataProvider('ofProvider')]
    public function testBasic(Date $date, Time $time): void
    {
        $datetime = LocalDateTime::of(date: $date, time: $time);

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
        self::assertEquals(\sprintf(
            '%s-%02d-%02dT%02d:%02d:%02d.%06d',
            $date->year(),
            $date->month()->number(),
            $date->day()->number(),
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
        ), $datetime->format('x-m-d\TH:i:s.u'));
        self::assertSame(LocalDateTime::of($date, $time), $datetime);
    }

    #[Depends('testBasic')]
    public function testFromNative(): void
    {
        $native = new \DateTimeImmutable('2025-12-31T12:15:30.000999+01:00');

        $actual = LocalDateTime::fromDateTime($native);

        self::assertEquals(2025, $actual->year()->number());
        self::assertEquals(12, $actual->month()->number());
        self::assertEquals(31, $actual->day()->number());
        self::assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        self::assertEquals(12, $actual->hour()->value());
        self::assertEquals(15, $actual->minute()->value());
        self::assertEquals(30, $actual->second()->second());
        self::assertEquals(999, $actual->second()->microsecond());
        self::assertSame($actual, LocalDateTime::fromDateTime($native));
    }

    #[Depends('testBasic')]
    public function testFromZoned(): void
    {
        $zoned = ZonedDateTime::of(
            date: Date::of(2025, 12, 31),
            time: Time::of(12, 15, Time\Second::of(30, 999)),
            timezone: new \DateTimeZone('+01:00'),
        );

        $actual = LocalDateTime::fromDateTime($zoned);

        self::assertEquals(2025, $actual->year()->number());
        self::assertEquals(12, $actual->month()->number());
        self::assertEquals(31, $actual->day()->number());
        self::assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        self::assertEquals(Date\DayOfYear::of(365), $actual->dayOfYear());
        self::assertEquals(12, $actual->hour()->value());
        self::assertEquals(15, $actual->minute()->value());
        self::assertEquals(30, $actual->second()->second());
        self::assertEquals(999, $actual->second()->microsecond());
        self::assertSame($actual, LocalDateTime::fromDateTime($zoned));
    }

    #[TestWith(['Y-m-d', '2025-12-31'])]
    #[TestWith(['H:i:s.u', '12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59'])]
    #[TestWith(['\Y-m-d H:i:sP', 'Y-12-31 12:15:59'])]
    #[TestWith([
        '\D\a\t\e: "l, d M y", \T\i\m\e: "G \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Date: "Wednesday, 31 Dec 25", Time: "12 hours, 15 minutes, 59 seconds"',
    ])]
    #[Depends('testBasic')]
    public function testFormat(string $format, string $expected): void
    {
        $datetime = LocalDateTime::of(
            date: Date::of(2025, 12, 31),
            time: Time::of(12, 15, Time\Second::last()),
        );

        $actual = $datetime->format($format);

        self::assertEquals($expected, $actual);
    }

    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:s.u', '1969-12-31T23:59:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59'])]
    #[TestWith(['\Y-m-d H:i:sP', 'Y-12-31 12:15:59'])]
    #[TestWith([
        '\D\a\t\e: "l, d M y", \T\i\m\e: "G \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Date: "Wednesday, 31 Dec 25", Time: "12 hours, 15 minutes, 59 seconds"',
    ])]
    #[Depends('testFormat')]
    public function testFromFormat(string $format, string $str): void
    {
        $datetime = LocalDateTime::fromFormat($format, $str);

        self::assertEquals($str, $datetime->format($format));
    }

    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31 12:15:59'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59 TZ'])]
    #[TestWith(['Y-m-d H:i', '2025-12-31 12:15:59'])]
    #[TestWith(['\Y-m-d H:i:sP', '2025-12-31 12:15:59'])]
    #[TestWith(['Y-m-d H:i:s', '2025-12-31'])]
    #[TestWith(['Y-m-d\TH:i:s.u', ''])]
    #[TestWith(['', '2025-12-31T12:15:59.999999'])]
    #[TestWith(['', ''])]
    #[Depends('testFromFormat')]
    public function testFromFormatFail(string $format, string $str): void
    {
        $this->expectException(FormatterError::class);
        LocalDateTime::fromFormat($format, $str);
    }

    #[TestWith(['2025-12-31T12:15:59.000999', [2025, 12, 31, 12, 15, 59, 999]])]
    #[TestWith(['2025-12-31T12:15:59.999999+00:00', [2025, 12, 31, 12, 15, 59, 999_999]])]
    #[TestWith(['2025-12-31 12:15:59.999999', [2025, 12, 31, 12, 15, 59, 999_999]])]
    #[TestWith(['2025-12-31 12:15:59', [2025, 12, 31, 12, 15, 59]])]
    #[TestWith(['2025-12-31 12:15', [2025, 12, 31, 12, 15, 0]])]
    #[TestWith(['12:15 31-12-2025', [2025, 12, 31, 12, 15, 0]])]
    #[TestWith(['15 Jan 25, 12:15:59', [2025, 1, 15, 12, 15, 59]])]
    #[TestWith(['1970-01-01 00:00:00.999999', [1970, 1, 1, 0, 0, 0, 999_999]])]
    #[TestWith(['1969-12-31 23:59:59.999999', [1969, 12, 31, 23, 59, 59, 999_999]])]
    #[TestWith(['0099-12-31 23:59:59.999999', [99, 12, 31, 23, 59, 59, 999_999]])]
    #[TestWith(['0000-12-31 23:59:59.999999', [0, 12, 31, 23, 59, 59, 999_999]])]
    #[TestWith(['9999-12-31 23:59:59.999999', [9999, 12, 31, 23, 59, 59, 999_999]])]
    #[TestWith(['-0099-12-31 23:59:59.999999', [-99, 12, 31, 23, 59, 59, 999_999]])]
    #[TestWith(['-0999-12-31 23:59:59.999999', [-999, 12, 31, 23, 59, 59, 999_999]])]
    #[TestWith(['-9999-12-31 23:59:59.999999', [-9999, 12, 31, 23, 59, 59, 999_999]])]
    #[Depends('testBasic')]
    public function testParse(string $str, array $expected): void
    {
        $expected = LocalDateTime::of(
            date: Date::of($expected[0], $expected[1], $expected[2]),
            time: Time::of($expected[3], $expected[4], Second::of($expected[5], $expected[6] ?? 0)),
        );
        $actual = LocalDateTime::parse($str);

        self::assertEquals($expected, $actual);
    }

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
        LocalDateTime::parse($str);
    }

    public static function comparisonProvider(): array
    {
        return [
            // LocalDateTime, Precision::Micro
            'Micro.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::EQUAL,
            ],
            'Micro.Microsecond.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Microsecond.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Hour.Greater' => [
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Month.Less' => [
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Month.Greater' => [
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Year.Less' => [
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Year.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Epoch.Equal' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 4545),
                self::localOf(1970, 1, 1, 0, 0, 0, 4545),
                Precision::Micro,
                self::EQUAL,
            ],
            'Micro.Epoch.Less' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 1),
                self::localOf(1970, 1, 1, 0, 0, 0, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Epoch.Greater' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
                Precision::Micro,
                self::GREATER,
            ],

            // LocalDateTime, Precision::Second
            'Second.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Microsecond.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Microsecond.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.Hour.Greater' => [
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'Second.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Month.Less' => [
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.Month.Greater' => [
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Year.Less' => [
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.Year.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Epoch.Equal' => [
                self::localOf(1969, 12, 31, 23, 59, 59, 4545),
                self::localOf(1969, 12, 31, 23, 59, 59, 1),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Epoch.Less' => [
                self::localOf(1969, 12, 31, 23, 59, 58, 4545),
                self::localOf(1969, 12, 31, 23, 59, 59, 4545),
                Precision::Second,
                self::LESS,
            ],
            'Second.Epoch.Greater' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
                Precision::Second,
                self::GREATER,
            ],

            // LocalDateTime, Precision::Minute
            'Minute.Second.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Microsecond.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Microsecond.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Hour.Greater' => [
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Month.Less' => [
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Month.Greater' => [
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Year.Less' => [
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Year.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            // LocalDateTime, Date and DateUnit
            'Date.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date::of(2025, 10, 30),
                Precision::Micro,
                self::EQUAL,
            ],
            'Date.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date::of(2025, 10, 31),
                Precision::Micro,
                self::LESS,
            ],
            'Date.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date::of(2025, 10, 29),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Year.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Year::of(2025),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Year.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Year::of(2026),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Year.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\Year::of(2024),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Month.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Month::of(10),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Month.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Month::of(11),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Month.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\Month::of(9),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.DayOfMonth.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfMonth::of(30),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfMonth::of(31),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\DayOfMonth::of(29),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.DayOfWeek.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfWeek::Thursday,
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.DayOfWeek.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfWeek::Friday,
                Precision::Micro,
                self::LESS,
            ],
            'Unit.DayOfWeek.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\DayOfWeek::Tuesday,
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.DayOYear.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfYear::of(303),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.DayOYear.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfYear::of(304),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.DayOYear.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\DayOfYear::of(302),
                Precision::Micro,
                self::GREATER,
            ],

            // LocalDateTime, Time and TimeUnit
            'Time.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time::of(12, 15, Second::of(55, 5555)),
                Precision::Micro,
                self::EQUAL,
            ],
            'Time.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time::of(12, 15, Second::of(55, 9999)),
                Precision::Micro,
                self::LESS,
            ],
            'Time.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Time::of(12, 15, Second::of(55)),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Hour.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Hour::of(12),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Hour::of(13),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Hour.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Time\Hour::of(11),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Minute.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Minute::of(15),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Minute::of(20),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Time\Minute::of(10),
                Precision::Micro,
                self::GREATER,
            ],
            'Unit.Second.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Second::of(55, 5555),
                Precision::Micro,
                self::EQUAL,
            ],
            'Unit.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Second::of(55, 999999),
                Precision::Micro,
                self::LESS,
            ],
            'Unit.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Time\Second::of(55),
                Precision::Micro,
                self::GREATER,
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(LocalDateTime $a, LocalDateTime|Unit $b, Precision $precision, int $expected): void
    {
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
            'Zero' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 50, 5555),
                Duration::zero(),
            ],
            'Micros.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5_500),
                self::localOf(2025, 12, 15, 12, 45, 55, 6_000),
                Duration::of(micros: 500),
            ],
            'Micros.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 6_000),
                self::localOf(2025, 12, 15, 12, 45, 55, 5_555),
                Duration::zero(),
            ],
            'Micros.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5_555),
                self::localOf(2025, 12, 15, 12, 45, 56, 4_555),
                Duration::of(micros: 999_000),
            ],
            'Micros.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 999_999),
                self::localOf(2025, 12, 15, 12, 45, 56, 0),
                Duration::of(micros: 1),
            ],
            'Seconds.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 0),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 4),
            ],
            'Seconds.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Seconds.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Minutes.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 55, 5555),
                Duration::of(minutes: 5),
            ],
            'Minutes.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 55, 0),
                Duration::of(minutes: 4, seconds: 59, micros: 994_445),
            ],
            'Minutes.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 50, 0),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'Hours' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 13, 45, 55, 5555),
                Duration::of(hours: 1),
            ],
            'Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 16, 12, 45, 55, 5555),
                Duration::of(days: 1),
            ],
            'Date.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date::of(2025, 12, 20),
                Duration::of(days: 5),
            ],
            'Date.Months' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date::of(2026, 1, 15),
                Duration::of(days: 31),
            ],
            'Unit.Year' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\Year::of(2026),
                Duration::of(days: 365),
            ],
            'Unit.Month' => [
                self::localOf(2025, 11, 15, 12, 45, 55, 5555),
                Date\Month::December,
                Duration::of(days: 30),
            ],
            'Unit.DayOfMonth' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'Unit.DayOfWeek' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'Unit.DayOfYear' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfYear::of(350),
                Duration::of(days: 1),
            ],
            'Time.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'Time.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Time.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Unit.Hour' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'Unit.Minute' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'Unit.Second.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'Unit.Second.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 0),
                Duration::of(seconds: 1, micros: 994_445),
            ],
            'Unit.Second.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, micro: 994_445),
                Duration::of(seconds: 2),
                Precision::Second,
            ],
            'Unit.Second.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57),
                Duration::of(seconds: 2),
                Precision::Second,
            ],
            'Unit.Second.4' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, micro: 994_445),
                Duration::zero(),
                Precision::Minute,
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('untilProvider')]
    public function testUntil(
        LocalDateTime $datetime,
        LocalDateTime|Unit $end,
        Duration $expected,
        Precision $precision = Precision::Micro,
    ): void {
        $actual = $datetime->until($end, $precision);

        self::assertEquals($expected, $actual);
    }

    public static function differenceProvider(): array
    {
        return [
            'Zero' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::zero(),
            ],
            'Seconds.0' => [
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Seconds.1' => [
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Seconds.2' => [
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::zero(),
                Precision::Minute,
            ],
            'Minutes.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 55, 5555),
                Duration::of(minutes: 5),
            ],
            'Minutes.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 0),
                self::localOf(2025, 12, 15, 12, 50, 54, 5555),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'Hours' => [
                self::localOf(2025, 12, 15, 13, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(hours: 1),
            ],
            'Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 16, 12, 45, 55, 5555),
                Duration::of(days: 1),
            ],
            'Date.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date::of(2025, 12, 20),
                Duration::of(days: 5),
            ],
            'Date.Months' => [
                self::localOf(2026, 1, 15, 12, 45, 55, 5555),
                Date::of(2025, 12, 15),
                Duration::of(days: 31),
            ],
            'Unit.Year' => [
                self::localOf(2026, 12, 15, 12, 45, 55, 5555),
                Date\Year::of(2025),
                Duration::of(days: 365),
            ],
            'Unit.Month' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\Month::November,
                Duration::of(days: 30),
            ],
            'Unit.DayOfMonth' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'Unit.DayOfWeek' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'Unit.DayOfYear' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfYear::of(348),
                Duration::of(days: 1),
            ],
            'Time.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'Time.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'Time.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'Time.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::zero(),
                Precision::Minute,
            ],
            'Unit.Hour' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'Unit.Minute' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'Unit.Second.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'Unit.Second.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57),
                Duration::of(seconds: 1, micros: 994_445),
            ],
            'Unit.Second.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 9999),
                Duration::zero(),
                Precision::Minute,
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('differenceProvider')]
    public function testDifference(
        LocalDateTime $datetime,
        LocalDateTime|Unit $end,
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
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::zero(),
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
            ],
            'Duration.Microseconds' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 500_000),
                Duration::of(micros: 505_555),
                self::localOf(2025, 12, 30, 12, 15, 31, 5555),
            ],
            'Duration.Seconds' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(seconds: 5),
                self::localOf(2025, 12, 30, 12, 15, 35, 5555),
            ],
            'Duration.Minutes' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(minutes: 65),
                self::localOf(2025, 12, 30, 13, 20, 30, 5555),
            ],
            'Duration.Hours.0' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(hours: 12, minutes: 30),
                self::localOf(2025, 12, 31, 0, 45, 30, 5555),
            ],
            'Duration.Hours.1' => [
                self::localOf(2006, 4, 2, 1, 15, 30, 5555),
                Duration::of(hours: 1, minutes: 30),
                self::localOf(2006, 4, 2, 2, 45, 30, 5555),
            ],
            'Duration.Days' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::localOf(2026, 1, 1, 0, 45, 30, 5555),
            ],
            'DateInterval.0' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::localOf(2026, 1, 1, 0, 45, 30, 5565),
            ],
            'DateInterval.1' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                new \DateInterval('PT36H30M'),
                self::localOf(2026, 1, 1, 0, 45, 30, 5555),
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('addProvider')]
    public function testAdd(LocalDateTime $datetime, Duration|\DateInterval $duration, LocalDateTime $expected): void
    {
        $actual = $datetime->add($duration);

        self::assertEquals($expected, $actual);
    }

    public static function subProvider(): array
    {
        return [
            'Duration.Zero' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::zero(),
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
            ],
            'Duration.Microseconds.0' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(micros: 6555),
                self::localOf(2025, 12, 30, 12, 15, 29, 999_000),
            ],
            'Duration.Microseconds.1' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                Duration::of(micros: 1),
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
            ],
            'Duration.Seconds' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(seconds: 5, micros: 1),
                self::localOf(2025, 12, 30, 12, 15, 25, 5554),
            ],
            'Duration.Minutes' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(minutes: 65),
                self::localOf(2025, 12, 30, 11, 10, 30, 5555),
            ],
            'Duration.Hours' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(hours: 12, minutes: 30),
                self::localOf(2025, 12, 29, 23, 45, 30, 5555),
            ],
            'Duration.Days' => [
                self::localOf(2026, 1, 1, 12, 15, 30, 5555),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::localOf(2025, 12, 30, 23, 45, 30, 5555),
            ],
            'DateInterval.0' => [
                self::localOf(2026, 1, 1, 12, 15, 30, 5555),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::localOf(2025, 12, 30, 23, 45, 30, 5545),
            ],
            'DateInterval.1' => [
                self::localOf(2026, 1, 1, 12, 15, 30, 5555),
                new \DateInterval('PT36H30M'),
                self::localOf(2025, 12, 30, 23, 45, 30, 5555),
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    #[DataProvider('subProvider')]
    public function testSub(LocalDateTime $datetime, Duration|\DateInterval $duration, LocalDateTime $expected): void
    {
        $actual = $datetime->sub($duration);

        self::assertEquals($expected, $actual);
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $datetime = LocalDateTime::of(Date::of(2025, 3, 24), Time::endOfDay());

        self::assertEquals('2025-03-24 23:59:59.999999', (string)$datetime);
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
}
