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

namespace Kronika\Tests\Range;

use Kronika\Date;
use Kronika\DateTime;
use Kronika\Duration;
use Kronika\LocalDateTime;
use Kronika\Precision;
use Kronika\Range\DateTimeRange;
use Kronika\Tests\DateTimeTest;
use Kronika\Tests\DurationTest;
use Kronika\Tests\LocalDateTimeTest;
use Kronika\Tests\ZonedDateTimeTest;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeRangeTest::class)]
final class DateTimeRangeTest extends TestCase
{
    public static function basicProvider(): array
    {
        return [
            [
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
                LocalDateTime::of(Date::of(2026, 1, 5), Time::of(12, 15, 30)),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::of(12, 15, 30),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/London'),
                ),
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::of(12, 15, 30),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
            [
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::of(12, 15, 30),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::of(12, 15, 30),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                LocalDateTime::of(Date::of(2026, 2, 15), Time::midnight()),
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DependsOnClass(LocalDateTimeTest::class)]
    #[DependsOnClass(ZonedDateTimeTest::class)]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('basicProvider')]
    public function testBasic(DateTime $since, DateTime $till): void
    {
        $duration = $since->until($till);
        $range = DateTimeRange::of($since, $till);

        self::assertEquals($since, $range->since());
        self::assertEquals($till, $range->till());
        self::assertTrue($range->contains($since));
        self::assertFalse($range->contains($till));
        self::assertEquals($duration, $range->duration());
        self::assertEquals($duration->isZero(), $range->isZero());
    }

    #[Depends('testBasic')]
    public function testAround(): void
    {
        $mid = LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay());
        $duration = Duration::ofWeek();
        $since = $mid->sub($duration);
        $till = $mid->add($duration);

        $range = DateTimeRange::around($mid, $duration);

        self::assertEquals($since, $range->since());
        self::assertEquals($till, $range->till());
        self::assertTrue($range->contains($since));
        self::assertFalse($range->contains($till));
        self::assertEquals($since->until($till), $range->duration());
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $since = LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay());
        $till = ZonedDateTime::of(
            Date::of(2026, 1, 1),
            Time::midday(),
            new \DateTimeZone('Europe/Berlin'),
        );
        $range = DateTimeRange::of($since, $till);

        self::assertEquals(\sprintf('%s – %s', $since, $till), (string)$range);
    }

    public static function eachProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.LocalDateTime.Days.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::midday()),
                    LocalDateTime::of(Date::of(2026, 1, 1), Time::midnight()),
                ),
                Duration::ofDay(),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::midday()),
                    LocalDateTime::of(Date::of(2025, 12, 29), Time::midday()),
                    LocalDateTime::of(Date::of(2025, 12, 30), Time::midday()),
                    LocalDateTime::of(Date::of(2025, 12, 31), Time::midday()),
                ],
            ],
            'LocalDateTime.LocalDateTime.Hours.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, Time\Second::of(35, 999)),
                    ),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 0)),
                ),
                Duration::ofHour(),
                [
                    LocalDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, Time\Second::of(35, 999)),
                    ),
                    LocalDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(13, 15, Time\Second::of(35, 999)),
                    ),
                    LocalDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(14, 15, Time\Second::of(35, 999)),
                    ),
                ],
            ],
            'LocalDateTime.LocalDateTime.Hours.1' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 30)),
                ),
                Duration::ofHour(),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(13, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(14, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 20, 35)),
                ],
            ],
            'LocalDateTime.LocalDateTime.Hours.2' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 30)),
                ),
                Duration::of(hours: 2),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(14, 20, 35)),
                ],
            ],
            'LocalDateTime.LocalDateTime.Minutes.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 15, 30)),
                ),
                Duration::of(minutes: 30),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 45, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(11, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(11, 45, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 15, 20)),
                ],
            ],
            'LocalDateTime.LocalDateTime.Minutes.1' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 15, 30)),
                    precision: Precision::Minute,
                ),
                Duration::of(minutes: 30),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 45, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(11, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(11, 45, 20)),
                ],
            ],
            'LocalDateTime.LocalDateTime.Seconds.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 25)),
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 21)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 22)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 23)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 24)),
                ],
            ],
            'LocalDateTime.LocalDateTime.Seconds.1' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
            'LocalDateTime.null.Seconds.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    till: null,
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
            'LocalDateTime.LocalDateTime.Zero.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 25)),
                ),
                Duration::zero(),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 21)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 22)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 23)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 24)),
                ],
            ],

            // ZonedDateTime
            'ZonedDateTime.ZonedDateTime.Days.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2026, 1, 1),
                        Time::midnight(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::ofDay(),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 29),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 30),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 31),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Hours.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, Time\Second::of(35, 999)),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(16, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                Duration::ofHour(),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, Time\Second::of(35, 999)),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(13, 15, Time\Second::of(35, 999)),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(14, 15, Time\Second::of(35, 999)),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Hours.1' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(15, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::ofHour(),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(13, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(14, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(15, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Hours.2' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(16, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                Duration::of(hours: 2),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(14, 20, 35),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Minutes.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(13, 15, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                Duration::of(minutes: 30),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 45, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(11, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(11, 45, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Minutes.1' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    precision: Precision::Minute,
                ),
                Duration::of(minutes: 30),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 45, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(11, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(11, 45, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 25),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::of(seconds: 1),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 21),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 22),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 23),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 24),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.1' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::of(seconds: 1),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.2' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(11, 15, 20),
                        new \DateTimeZone('+02:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::of(seconds: 1),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(11, 15, 20),
                        new \DateTimeZone('+02:00'),
                    ),
                ],
            ],
            'ZonedDateTime.null.Seconds.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+02:00'),
                    ),
                    till: null,
                ),
                Duration::of(seconds: 1),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+02:00'),
                    ),
                ],
            ],
            'ZonedDateTime.ZonedDateTime.Zero.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 25),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::zero(),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 21),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 22),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 23),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 24),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],

            // LocalDateTime vs ZonedDateTime
            'LocalDateTime.ZonedDateTime.Hours.0' => [
                DateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(14, 30, 45)),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(15, 35, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                Duration::ofHour(),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(14, 30, 45)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 30, 45)),
                ],
            ],
            'ZonedDateTime.LocalDateTime.Hours.0' => [
                DateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(14, 30, 45),
                        new \DateTimeZone('+01:00'),
                    ),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 35, 20)),
                ),
                Duration::ofHour(),
                [
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(14, 30, 45),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(15, 30, 45),
                        new \DateTimeZone('+01:00'),
                    ),
                ],
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('eachProvider')]
    public function testEach(DateTimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->each($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }
}
