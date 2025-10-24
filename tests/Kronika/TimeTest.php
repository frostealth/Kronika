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
use PHPUnit\Framework\TestCase;

final class TimeTest extends TestCase
{
    public static function ofProvider(): array
    {
        return [
            [Time\Hour::zero(), Time\Minute::zero(), Time\Second::zero()],
            [Time\Hour::of(5), Time\Minute::of(10), Time\Second::of(15)],
            [Time\Hour::of(9), Time\Minute::of(25), Time\Second::of(35, 9683)],
            [Time\Hour::of(12), Time\Minute::of(0), Time\Second::of(0)],
            [Time\Hour::of(15), Time\Minute::of(49), Time\Second::of(54, 429)],
            [Time\Hour::of(21), Time\Minute::of(57), Time\Second::of(42, 791472)],
            [Time\Hour::last(), Time\Minute::last(), Time\Second::last()],
        ];
    }

    #[DataProvider('ofProvider')]
    public function testBasic(Time\Hour $hour, Time\Minute $minute, Time\Second $second): void
    {
        $time = Time::of(hour: $hour, minute: $minute, second: $second);

        $this->assertEquals($hour, $time->hour());
        $this->assertEquals($minute, $time->minute());
        $this->assertEquals($second, $time->second());
        $this->assertEquals($time, Time::of(hour: $hour->value(), minute: $minute->value(), second: $second));
        $this->assertEquals(
            $time->with($time->second()->resetMicro()),
            Time::of(hour: $hour->value(), minute: $minute->value(), second: $second->second()),
        );
    }

    public static function withProvider(): array
    {
        return [
            [Time::midnight(), Time\Hour::last(), Time::of(hour: 23, minute: 0)],
            [Time::noon(), Time\Hour::zero(), Time::of(hour: 0, minute: 0)],
            [Time::endOfDay(), Time\Hour::of(20), Time::of(hour: 20, minute: 59, second: Time\Second::of(59, 999_999))],
            [Time::of(hour: 14, minute: 30, second: 45), Time\Hour::of(21), Time::of(hour: 21, minute: 30, second: 45)],
            [Time::of(hour: 14, minute: 30, second: 45), Time\Minute::of(21), Time::of(hour: 14, minute: 21, second: 45)],
            [Time::of(hour: 14, minute: 30, second: 45), Time\Second::of(18), Time::of(hour: 14, minute: 30, second: 18)],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(Time $time, Time\TimeUnit $unit, Time $expected): void
    {
        $result = $time->with($unit);

        $this->assertEquals($expected, $result);
    }

    public static function formatProvider(): array
    {
        return [
            [Time::of(hour: 22, minute: 7, second: Time\Second::of(second: 42, micro: 8647)), 'H:i:s.u', '22:07:42.008647'],
            [Time::of(hour: 22, minute: 7, second: Time\Second::of(second: 42, micro: 8647)), 'H:i:s', '22:07:42'],
            [Time::of(hour: 2, minute: 7, second: Time\Second::of(second: 8, micro: 1)), 'H:i:s.u', '02:07:08.000001'],
            [Time::of(hour: 2, minute: 7, second: Time\Second::of(second: 8, micro: 1)), 'H i s', '02 07 08'],
            [Time::of(hour: 2, minute: 7, second: Time\Second::of(second: 8, micro: 1)), 'i', '07'],
            [Time::of(hour: 2, minute: 7, second: Time\Second::of(second: 8, micro: 1)), 'Y-m-d', 'Y-m-d'],
            [Time::of(hour: 2, minute: 7, second: Time\Second::of(second: 8, micro: 1)), 'Y m d', 'Y m d'],
            [Time::of(hour: 2, minute: 7, second: Time\Second::of(second: 8, micro: 1)), 'd/m/Y', 'd/m/Y'],
        ];
    }

    #[DataProvider('formatProvider')]
    public function testFormat(Time $time, string $format, string $expected): void
    {
        $this->assertEquals($expected, $time->format($format));
    }

    public static function ofDateTimeProvider(): array
    {
        return [
            [
                new \DateTime('1985-04-28 12:46:12.123456 UTC'),
                Time::of(hour: 12, minute: 46, second: Time\Second::of(second: 12, micro: 123456)),
            ],
            [new \DateTime('1985-05-21 02:06:02 +02:00'), Time::of(hour: 2, minute: 6, second: 2)],
            [new \DateTime('2020-07-01'), Time::midnight()],
            [
                new \DateTimeImmutable('1985-04-28 12:46:12.123456 UTC'),
                Time::of(hour: 12, minute: 46, second: Time\Second::of(second: 12, micro: 123456)),
            ],
            [new \DateTimeImmutable('1985-05-21 12:46:12 +02:00'), Time::of(hour: 12, minute: 46, second: 12)],
            [new \DateTimeImmutable('2020-07-01'), Time::midnight()],
            [new \DateTimeImmutable('23:59:59.999999'), Time::endOfDay()],
            [
                LocalDateTime::of(Date::of(1985, 4, 28), Time::of(12, 46, 12)),
                Time::of(hour: 12, minute: 46, second: 12),
            ],
            [
                ZonedDateTime::of(
                    Date::of(1985, 4, 28),
                    Time::of(12, 46, 12),
                    new \DateTimeZone('+10:00'),
                ),
                Time::of(hour: 12, minute: 46, second: 12),
            ],
            [Time::of(hour: 8, minute: 54, second: 39), Time::of(hour: 8, minute: 54, second: 39)],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('ofDateTimeProvider')]
    public function ofDateTime(\DateTimeInterface|Time $input, Time $expected): void
    {
        $actual = Time::ofDateTime($input);

        $this->assertEquals($expected, $actual);
    }

    public function testToString(): void
    {
        $time = Time::of(hour: 17, minute: 5, second: Time\Second::of(second: 39, micro: 4582));

        $this->assertEquals('17:05:39.004582', (string) $time);
    }
}
