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
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalDateTime::class)]
final class LocalDateTimeTest extends TestCase
{
    public static function ofProvider(): array
    {
        return [
            [Date::of(2025, 3, 24), Time::midday()],
            [Date::of(1950, 2, 28), Time::endOfDay()],
            [Date::of(3000, 12, 10), Time::midnight()],
            [Date::of(500, 1, 15), Time::of(9, 45, 24)],
            [Date::of(2950, 11, 21), Time::of(23, 45, Time\Second::of(35, 4455))],
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
        self::assertSame($time, $datetime->time());
        self::assertSame($time->hour(), $datetime->hour());
        self::assertSame($time->minute(), $datetime->minute());
        self::assertSame($time->second(), $datetime->second());
        self::assertEquals(\sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d.%06d',
            $date->year()->number(),
            $date->month()->number(),
            $date->day()->number(),
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
        ), $datetime->format('Y-m-d\TH:i:s.u'));
        self::assertSame(LocalDateTime::of($date, $time), $datetime);
    }

    #[Depends('testBasic')]
    public function testOfNative(): void
    {
        $native = new \DateTimeImmutable('2025-12-31T12:15:30.000999+01:00');

        $actual = LocalDateTime::ofDateTime($native);

        self::assertEquals(2025, $actual->year()->number());
        self::assertEquals(12, $actual->month()->number());
        self::assertEquals(31, $actual->day()->number());
        self::assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        self::assertEquals(12, $actual->hour()->value());
        self::assertEquals(15, $actual->minute()->value());
        self::assertEquals(30, $actual->second()->second());
        self::assertEquals(999, $actual->second()->microsecond());
        self::assertSame($actual, LocalDateTime::ofDateTime($native));
    }

    #[Depends('testBasic')]
    public function testOfZoned(): void
    {
        $zoned = ZonedDateTime::of(
            date: Date::of(2025, 12, 31),
            time: Time::of(12, 15, Time\Second::of(30, 999)),
            timezone: new \DateTimeZone('+01:00'),
        );

        $actual = LocalDateTime::ofDateTime($zoned);

        self::assertEquals(2025, $actual->year()->number());
        self::assertEquals(12, $actual->month()->number());
        self::assertEquals(31, $actual->day()->number());
        self::assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        self::assertEquals(12, $actual->hour()->value());
        self::assertEquals(15, $actual->minute()->value());
        self::assertEquals(30, $actual->second()->second());
        self::assertEquals(999, $actual->second()->microsecond());
        self::assertSame($actual, LocalDateTime::ofDateTime($zoned));
    }

    #[TestWith(['Y-m-d', '2025-12-31'])]
    #[TestWith(['H:i:s.u', '12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:s.u', '2025-12-31T12:15:59.999999'])]
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59P'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59P'])]
    #[TestWith(['\Y-m-d H:i:sP', 'Y-12-31 12:15:59P'])]
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
    #[TestWith(['Y-m-d\TH:i:sP', '2025-12-31T12:15:59P'])]
    #[TestWith(['Y-m-d H:i:sP', '2025-12-31 12:15:59P'])]
    #[TestWith(['\Y-m-d H:i:sP', 'Y-12-31 12:15:59P'])]
    #[TestWith([
        '\D\a\t\e: "l, d M y", \T\i\m\e: "G \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Date: "Wednesday, 31 Dec 25", Time: "12 hours, 15 minutes, 59 seconds"',
    ])]
    #[Depends('testFormat')]
    public function testOfFormat(string $format, string $str): void
    {
        $datetime = LocalDateTime::ofFormat($format, $str);

        self::assertEquals($str, $datetime->format($format));
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $datetime = LocalDateTime::of(Date::of(2025, 3, 24), Time::endOfDay());

        self::assertEquals('2025-03-24T23:59:59.999999', (string)$datetime);
    }
}
