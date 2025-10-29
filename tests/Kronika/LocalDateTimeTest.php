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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\TestCase;

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

        $this->assertSame($date, $datetime->date());
        $this->assertSame($date->year(), $datetime->year());
        $this->assertSame($date->month(), $datetime->month());
        $this->assertSame($date->day(), $datetime->day());
        $this->assertSame($date->dayOfWeek(), $datetime->dayOfWeek());
        $this->assertSame($time, $datetime->time());
        $this->assertSame($time->hour(), $datetime->hour());
        $this->assertSame($time->minute(), $datetime->minute());
        $this->assertSame($time->second(), $datetime->second());
        $this->assertEquals(\sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d.%06d',
            $date->year()->number(),
            $date->month()->number(),
            $date->day()->number(),
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
        ), $datetime->format('Y-m-d\TH:i:s.u'));
        $this->assertSame(LocalDateTime::of($date, $time), $datetime);
    }

    #[Depends('testBasic')]
    public function testOfNative(): void
    {
        $native = new \DateTimeImmutable('2025-12-31T12:15:30.000999+01:00');

        $actual = LocalDateTime::ofDateTime($native);

        $this->assertEquals(2025, $actual->year()->number());
        $this->assertEquals(12, $actual->month()->number());
        $this->assertEquals(31, $actual->day()->number());
        $this->assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        $this->assertEquals(12, $actual->hour()->value());
        $this->assertEquals(15, $actual->minute()->value());
        $this->assertEquals(30, $actual->second()->second());
        $this->assertEquals(999, $actual->second()->microsecond());
        $this->assertSame($actual, LocalDateTime::ofDateTime($native));
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

        $this->assertEquals(2025, $actual->year()->number());
        $this->assertEquals(12, $actual->month()->number());
        $this->assertEquals(31, $actual->day()->number());
        $this->assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        $this->assertEquals(12, $actual->hour()->value());
        $this->assertEquals(15, $actual->minute()->value());
        $this->assertEquals(30, $actual->second()->second());
        $this->assertEquals(999, $actual->second()->microsecond());
        $this->assertSame($actual, LocalDateTime::ofDateTime($zoned));
    }

    #[Depends('testBasic')]
    public function testMidnightOf(): void
    {
        $date = Date::of(2025, 12, 31);

        $actual = LocalDateTime::midnightOf($date);

        $this->assertEquals(2025, $actual->year()->number());
        $this->assertEquals(12, $actual->month()->number());
        $this->assertEquals(31, $actual->day()->number());
        $this->assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        $this->assertEquals(0, $actual->hour()->value());
        $this->assertEquals(0, $actual->minute()->value());
        $this->assertEquals(0, $actual->second()->second());
        $this->assertEquals(0, $actual->second()->microsecond());
        $this->assertSame($actual, LocalDateTime::midnightOf($date));
    }

    #[Depends('testBasic')]
    public function testMiddayOf(): void
    {
        $date = Date::of(2025, 12, 31);

        $actual = LocalDateTime::middayOf($date);

        $this->assertEquals(2025, $actual->year()->number());
        $this->assertEquals(12, $actual->month()->number());
        $this->assertEquals(31, $actual->day()->number());
        $this->assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        $this->assertEquals(12, $actual->hour()->value());
        $this->assertEquals(0, $actual->minute()->value());
        $this->assertEquals(0, $actual->second()->second());
        $this->assertEquals(0, $actual->second()->microsecond());
        $this->assertSame($actual, LocalDateTime::middayOf($date));
    }

    #[Depends('testBasic')]
    public function testEndOfDay(): void
    {
        $date = Date::of(2025, 12, 31);

        $actual = LocalDateTime::endOfDayOf($date);

        $this->assertEquals(2025, $actual->year()->number());
        $this->assertEquals(12, $actual->month()->number());
        $this->assertEquals(31, $actual->day()->number());
        $this->assertEquals(Date\DayOfWeek::Wednesday, $actual->dayOfWeek());
        $this->assertEquals(23, $actual->hour()->value());
        $this->assertEquals(59, $actual->minute()->value());
        $this->assertEquals(59, $actual->second()->second());
        $this->assertEquals(999999, $actual->second()->microsecond());
        $this->assertSame($actual, LocalDateTime::endOfDayOf($date));
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $datetime = LocalDateTime::of(Date::of(2025, 3, 24), Time::endOfDay());

        $this->assertEquals('2025-03-24T23:59:59.999999', (string)$datetime);
    }
}
