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
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Unit;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\TestCase;

final class ZonedDateTimeTest extends TestCase
{
    public static function ofProvider(): array
    {
        return [
            [Date::of(2025, 3, 24), Time::midday(), new \DateTimeZone('-02:30')],
            [Date::of(1950, 2, 28), Time::endOfDay(), new \DateTimeZone('-05:00')],
            [Date::of(3000, 12, 10), Time::midnight(), new \DateTimeZone('-05:00')],
            [Date::of(500, 1, 15), Time::of(9, 45, 24), new \DateTimeZone('+05:00')],
        ];
    }

    #[DependsExternal(DateTest::class, 'testBasic')]
    #[DependsExternal(TimeTest::class, 'testBasic')]
    #[DataProvider('ofProvider')]
    public function testBasic(Date $date, Time $time, \DateTimeZone $timezone): void
    {
        $datetime = ZonedDateTime::of(date: $date, time: $time, timezone: $timezone);

        $this->assertSame($date, $datetime->date());
        $this->assertSame($date->year(), $datetime->year());
        $this->assertSame($date->month(), $datetime->month());
        $this->assertSame($date->day(), $datetime->day());
        $this->assertSame($date->dayOfWeek(), $datetime->dayOfWeek());
        $this->assertSame($time, $datetime->time());
        $this->assertSame($time->hour(), $datetime->hour());
        $this->assertSame($time->minute(), $datetime->minute());
        $this->assertSame($time->second(), $datetime->second());
        $this->assertEquals($time->second()->microsecond(), $datetime->microsecond());
        $this->assertEquals($timezone->getName(), $datetime->timezone()->getName());
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
    }

    public static function withProvider(): array
    {
        return [
            [Date::of(1834, 6, 25)],
            [Date\Year::of(1999)],
            [Date\Month::November],
            [Date\DayOfMonth::of(14)],
            [Date\DayOfMonth::of(31)],
            [Date\DayOfWeek::Monday],
            [Date\DayOfWeek::Wednesday],
            [Date\DayOfWeek::Sunday],
            [Time::midnight()],
            [Time\Hour::of(14)],
            [Time\Minute::of(30)],
            [Time\Second::of(45)],
            [Time\Second::of(15, 8765)],
            [new \DateTimeZone('UTC')],
            [new \DateTimeZone('+08:30')],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(Unit|\DateTimeZone $unit): void
    {
        $date = Date::of(2025, 3, 24);
        $time = Time::endOfDay();
        $timezone = new \DateTimeZone('-05:00');
        $zoned = ZonedDateTime::of($date, $time, $timezone);

        $result = $zoned->with($unit);

        if ($unit instanceof \DateTimeZone) {
            $this->assertSame($date, $result->date());
            $this->assertSame($time, $result->time());
            $this->assertEquals($unit->getName(), $result->timezone()->getName());
        } elseif ($unit instanceof Date) {
            $this->assertSame($unit, $result->date());
            $this->assertSame($time, $result->time());
            $this->assertEquals($timezone->getName(), $result->timezone()->getName());
        } elseif ($unit instanceof Date\DateUnit) {
            $this->assertSame($date->with($unit), $result->date());
            $this->assertSame($time, $result->time());
            $this->assertEquals($timezone->getName(), $result->timezone()->getName());
        } elseif ($unit instanceof Time) {
            $this->assertSame($date, $result->date());
            $this->assertSame($unit, $result->time());
            $this->assertEquals($timezone->getName(), $result->timezone()->getName());
        } elseif ($unit instanceof Time\TimeUnit) {
            $this->assertSame($date, $result->date());
            $this->assertSame($time->with($unit), $result->time());
            $this->assertEquals($timezone->getName(), $result->timezone()->getName());
        }
    }

    public function testToLocalDateTime(): void
    {
        $local = LocalDateTime::of(Date::of(2025, 3, 24), Time::of(14, 8, 47));
        $zoned = ZonedDateTime::ofLocal($local, new \DateTimeZone('-02:30'));

        $this->assertInstanceOf(LocalDateTime::class, $zoned->toLocalDateTime());
        $this->assertSame($local, $zoned->toLocalDateTime());
    }

    public function testToString(): void
    {
        $datetime = ZonedDateTime::of(
            Date::of(2025, 3, 24),
            Time::endOfDay(),
            new \DateTimeZone('+01:30'),
        );

        $this->assertEquals('2025-03-24T23:59:59+01:30', (string) $datetime);
    }

    #[Depends('testBasic')]
    public function testCreateFromInterface(): void
    {
        $datetime = ZonedDateTime::createFromInterface(new \DateTimeImmutable('2025-03-24T23:09:59.123456+01:30'));

        $this->assertEquals('2025-03-24T23:09:59.123456+0130', $datetime->format('Y-m-d\TH:i:s.uO'));

        $this->assertEquals(2025, $datetime->year()->number());
        $this->assertEquals(3, $datetime->month()->number());
        $this->assertEquals(24, $datetime->day()->number());

        $this->assertEquals(23, $datetime->hour()->value());
        $this->assertEquals(9, $datetime->minute()->value());
        $this->assertEquals(59.123456, $datetime->second()->value());
        $this->assertEquals(123456, $datetime->microsecond());
        $this->assertEquals(123456, $datetime->getMicrosecond());

        $this->assertEquals('+01:30', $datetime->timezone()->getName());
        $this->assertEquals('+01:30', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromMutable(): void
    {
        $datetime = ZonedDateTime::createFromMutable(new \DateTime('2025-03-24T23:09:59.123456+01:30'));

        $this->assertEquals('2025-03-24T23:09:59.123456+0130', $datetime->format('Y-m-d\TH:i:s.uO'));

        $this->assertEquals(2025, $datetime->year()->number());
        $this->assertEquals(3, $datetime->month()->number());
        $this->assertEquals(24, $datetime->day()->number());

        $this->assertEquals(23, $datetime->hour()->value());
        $this->assertEquals(9, $datetime->minute()->value());
        $this->assertEquals(59.123456, $datetime->second()->value());
        $this->assertEquals(123456, $datetime->microsecond());
        $this->assertEquals(123456, $datetime->getMicrosecond());

        $this->assertEquals('+01:30', $datetime->timezone()->getName());
        $this->assertEquals('+01:30', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromFormat(): void
    {
        $datetime = ZonedDateTime::createFromFormat(
            'Y-m-d\TH:i:s.uP',
            '2025-03-24T23:09:59.123456+01:30',
        );

        $this->assertEquals('2025-03-24T23:09:59.123456+0130', $datetime->format('Y-m-d\TH:i:s.uO'));

        $this->assertEquals(2025, $datetime->year()->number());
        $this->assertEquals(3, $datetime->month()->number());
        $this->assertEquals(24, $datetime->day()->number());

        $this->assertEquals(23, $datetime->hour()->value());
        $this->assertEquals(9, $datetime->minute()->value());
        $this->assertEquals(59.123456, $datetime->second()->value());
        $this->assertEquals(123456, $datetime->microsecond());
        $this->assertEquals(123456, $datetime->getMicrosecond());

        $this->assertEquals('+01:30', $datetime->timezone()->getName());
        $this->assertEquals('+01:30', $datetime->getTimezone()->getName());
    }

    #[Depends('testBasic')]
    public function testCreateFromTimestamp(): void
    {
        $datetime = ZonedDateTime::createFromTimestamp(
            new \DateTimeImmutable('2025-03-24T23:09:59.123456+01:30')->getTimestamp(),
        );

        $this->assertEquals('2025-03-24T21:39:59.000000+0000', $datetime->format('Y-m-d\TH:i:s.uO'));

        $this->assertEquals(2025, $datetime->year()->number());
        $this->assertEquals(3, $datetime->month()->number());
        $this->assertEquals(24, $datetime->day()->number());

        $this->assertEquals(21, $datetime->hour()->value());
        $this->assertEquals(39, $datetime->minute()->value());
        $this->assertEquals(59.0, $datetime->second()->value());
        $this->assertEquals(0, $datetime->microsecond());
        $this->assertEquals(0, $datetime->getMicrosecond());

        $this->assertEquals('UTC', $datetime->timezone()->getName());
        $this->assertEquals('UTC', $datetime->getTimezone()->getName());
    }

    public function testNativeMethods(): void
    {
        $native = new \DateTimeImmutable('2025-03-24T23:09:59.123456+01:30');
        $kronika = ZonedDateTime::of(
            Date::of(2025, 3, 24),
            Time::of(23, 9, Time\Second::of(59, 123456)),
            new \DateTimeZone('+01:30'),
        );
        $format = 'Y-m-d\TH:i:s.uO';

        $this->assertEquals($native->getTimestamp(), $kronika->getTimestamp());
        $this->assertEquals($native->getTimezone()->getName(), $kronika->getTimezone()->getName());
        $this->assertEquals($native->getMicrosecond(), $kronika->getMicrosecond());
        $this->assertEquals($native->getOffset(), $kronika->getOffset());
        $this->assertEquals(
            $native->setTime(9, 45, 32)->format($format),
            $kronika->setTime(9, 45, 32)->format($format),
        );
        $this->assertEquals(
            $native->setDate(1982, 11, 24)->format($format),
            $kronika->setDate(1982, 11, 24)->format($format),
        );
        $this->assertEquals(
            $native->setISODate(1500, 42, 3)->format($format),
            $kronika->setISODate(1500, 42, 3)->format($format),
        );
        $this->assertEquals(
            $native->setTimezone(new \DateTimeZone('-01:30'))->format($format),
            $kronika->setTimezone(new \DateTimeZone('-01:30'))->format($format),
        );
        $this->assertEquals(
            $native->setMicrosecond(999)->format($format),
            $kronika->setMicrosecond(999)->format($format),
        );
        $this->assertEquals(
            $native->add(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->add(new \DateInterval('P3DT2H23M13S'))->format($format),
        );
        $this->assertEquals(
            $native->add(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->add(Duration::of(days: 3, hours: 2, minutes: 23, seconds: 13))->format($format),
        );
        $this->assertEquals(
            $native->sub(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->sub(new \DateInterval('P3DT2H23M13S'))->format($format),
        );
        $this->assertEquals(
            $native->sub(new \DateInterval('P3DT2H23M13S'))->format($format),
            $kronika->sub(Duration::of(days: 3, hours: 2, minutes: 23, seconds: 13))->format($format),
        );
        $this->assertEquals(
            $native->modify('+3 days')->format($format),
            $kronika->modify('+3 days')->format($format),
        );
        $this->assertEquals(
            $native->diff(new \DateTime('2030-09-28 05:25:10 UTC'))->format($format),
            $kronika->diff(new \DateTime('2030-09-28 05:25:10 UTC'))->format($format),
        );
    }

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
        $this->assertEquals($native->format($format), $result->format($format));
    }

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
        $this->assertEquals($native->format($format), $result->format($format));
    }
}
