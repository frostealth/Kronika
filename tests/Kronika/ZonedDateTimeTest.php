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
    #[DependsOnClass(LocalDateTimeTest::class)]
    #[DataProvider('ofProvider')]
    public function testBasic(Date $date, Time $time, \DateTimeZone $timezone): void
    {
        $datetime = ZonedDateTime::of(date: $date, time: $time, timezone: $timezone);

        self::assertSame($date, $datetime->date());
        self::assertSame($date->year(), $datetime->year());
        self::assertSame($date->month(), $datetime->month());
        self::assertSame($date->day(), $datetime->day());
        self::assertSame($date->dayOfWeek(), $datetime->dayOfWeek());
        self::assertSame($time, $datetime->time());
        self::assertSame($time->hour(), $datetime->hour());
        self::assertSame($time->minute(), $datetime->minute());
        self::assertSame($time->second(), $datetime->second());
        self::assertEquals($time->second()->microsecond(), $datetime->microsecond());
        self::assertEquals($timezone->getName(), $datetime->timezone()->getName());
        self::assertEquals(\sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d.%06d%s',
            $date->year()->number(),
            $date->month()->number(),
            $date->day()->number(),
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
            $timezone->getName(),
        ), $datetime->format('Y-m-d\TH:i:s.uP'));
        self::assertSame(ZonedDateTime::of($date, $time, $timezone), $datetime);
    }

    #[Depends('testBasic')]
    public function testShiftTimezone(): void
    {
        $datetime = ZonedDateTime::of(
            date: Date::of(2025, 12, 31),
            time: Time::of(12, 15, 30),
            timezone: new \DateTimeZone('+01:00'),
        );

        $actual = $datetime->shiftTimezone(new \DateTimeZone('+02:30'));

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
    public function testOfFormat(string $format, string $str): void
    {
        $datetime = ZonedDateTime::ofFormat($format, $str);

        self::assertEquals($str, $datetime->format($format));
    }

    #[Depends('testBasic')]
    public function testToLocalDateTime(): void
    {
        $local = LocalDateTime::of(Date::of(2025, 3, 24), Time::of(14, 8, 47));
        $zoned = ZonedDateTime::ofLocal($local, new \DateTimeZone('-02:30'));

        self::assertInstanceOf(LocalDateTime::class, $zoned->toLocalDateTime());
        self::assertSame($local, $zoned->toLocalDateTime());
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $datetime = ZonedDateTime::of(
            Date::of(2025, 3, 24),
            Time::endOfDay(),
            new \DateTimeZone('+01:30'),
        );

        self::assertEquals('2025-03-24T23:59:59.999999+01:30', (string) $datetime);
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

        self::assertEquals('+01:30', $datetime->timezone()->getName());
        self::assertEquals('+01:30', $datetime->getTimezone()->getName());
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
}
