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
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ZonedDateTimeTest extends TestCase
{
    public static function ofProvider(): array
    {
        return [
            [Date::of(2025, 3, 24), Time::noon(), new \DateTimeZone('-02:30')],
        ];
    }

    #[DataProvider('ofProvider')]
    public function testBasic(Date $date, Time $time, \DateTimeZone $timezone): void
    {
        $datetime = ZonedDateTime::of(date: $date, time: $time, timezone: $timezone);

        $this->assertEquals($date, $datetime->date());
        $this->assertEquals($date->year(), $datetime->year());
        $this->assertEquals($date->month(), $datetime->month());
        $this->assertEquals($date->day(), $datetime->day());
        $this->assertEquals($date->dayOfWeek(), $datetime->dayOfWeek());
        $this->assertEquals($time, $datetime->time());
        $this->assertEquals($time->hour(), $datetime->hour());
        $this->assertEquals($time->minute(), $datetime->minute());
        $this->assertEquals($time->second(), $datetime->second());
        $this->assertEquals($time->second()->microsecond(), $datetime->microsecond());
        $this->assertEquals($timezone->getName(), $datetime->timezone()->getName());
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
}
