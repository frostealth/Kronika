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
use Kronika\Range\DateRange;
use Kronika\Range\DateTimeRange;
use Kronika\Range\Exception\Overlap;
use Kronika\Range\Exception\NoOverlap;
use Kronika\Range\LocalDateTimeRange;
use Kronika\Range\ZonedDateTimeRange;
use Kronika\Tests\DateTimeTest;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeRangeTest::class)]
final class DateTimeRangeTest extends TestCase
{
    public static function eachProvider(): array
    {
        return [
            // LocalDateTimeRange
            'LocalDateTime.Days.0' => [
                LocalDateTimeRange::of(
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
            'LocalDateTime.Hours.0' => [
                LocalDateTimeRange::of(
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
            'LocalDateTime.Hours.1' => [
                LocalDateTimeRange::of(
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
            'LocalDateTime.Hours.2' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(15, 30)),
                ),
                Duration::of(hours: 2),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 20, 35)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(14, 20, 35)),
                ],
            ],
            'LocalDateTime.Minutes.0' => [
                LocalDateTimeRange::of(
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
            'LocalDateTime.Minutes.1' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(12, 15, 18)),
                ),
                Duration::of(minutes: 30),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 45, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(11, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(11, 45, 20)),
                ],
            ],
            'LocalDateTime.Seconds.0' => [
                LocalDateTimeRange::of(
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
            'LocalDateTime.Seconds.1' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
            'LocalDateTime.null.Seconds.0' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    to: null,
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
            'LocalDateTime.Zero.0' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 25)),
                ),
                Duration::zero(),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],

            // ZonedDateTimeRange
            'ZonedDateTime.Days.0' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Hours.0' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Hours.1' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Hours.2' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Minutes.0' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Minutes.1' => [
                ZonedDateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+01:00'),
                    ),
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(12, 15, 18),
                        new \DateTimeZone('+01:00'),
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
                ],
            ],
            'ZonedDateTime.Seconds.0' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Seconds.1' => [
                ZonedDateTimeRange::of(
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
            'ZonedDateTime.Seconds.2' => [
                ZonedDateTimeRange::of(
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
                ZonedDateTimeRange::of(
                    ZonedDateTime::of(
                        Date::of(2025, 12, 28),
                        Time::of(10, 15, 20),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: null,
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
            'ZonedDateTime.Zero.0' => [
                ZonedDateTimeRange::of(
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
                ],
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('eachProvider')]
    public function testEach(DateTimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->each($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function splitProvider(): array
    {
        return [
            // LocalDateTimeRange
            'Local.0' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                ),
                'step' => Duration::ofHour(),
                'expected' => [
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                        ),
                    ),
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(14, 0),
                        ),
                    ),
                ],
            ],
            'Local.1' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                'step' => Duration::ofHour(),
                'expected' => [
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                        ),
                    ),
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 30),
                        ),
                    ),
                ],
            ],
            'Local.2' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                'step' => Duration::ofDay(),
                'expected' => [
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 30),
                        ),
                    ),
                ],
            ],
            'Local.3' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                'step' => Duration::zero(),
                'expected' => [
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 30),
                        ),
                    ),
                ],
            ],
            'Local.4' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'step' => Duration::ofSecond(),
                'expected' => [
                    LocalDateTimeRange::of(
                        from: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                        ),
                        to: LocalDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                        ),
                    ),
                ],
            ],

            // ZonedDateTimeRange
            'Zoned.0' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'step' => Duration::ofHour(),
                'expected' => [
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(14, 0),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                ],
            ],
            'Zoned.1' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'step' => Duration::ofHour(),
                'expected' => [
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 0),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 30),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                ],
            ],
            'Zoned.2' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'step' => Duration::ofDay(),
                'expected' => [
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 30),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                ],
            ],
            'Zoned.3' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'step' => Duration::zero(),
                'expected' => [
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::of(13, 30),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                ],
            ],
            'Zoned.4' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'step' => Duration::ofSecond(),
                'expected' => [
                    ZonedDateTimeRange::of(
                        from: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                            new \DateTimeZone('+02:00'),
                        ),
                        to: ZonedDateTime::of(
                            Date::of(2025, 12, 15),
                            Time::midday(),
                            new \DateTimeZone('+02:00'),
                        ),
                    ),
                ],
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('splitProvider')]
    public function testSplit(DateTimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->split($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function toDateRangeProvider(): array
    {
        return [
            'Local.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 20), Time::midnight()),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 15),
                    to: Date::of(2025, 12, 20),
                ),
            ],
            'Zoned.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 20),
                        Time::midnight(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 15),
                    to: Date::of(2025, 12, 20),
                ),
            ],
            'Zoned.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('America/Los_Angeles'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 20),
                        Time::midnight(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 15),
                    to: Date::of(2025, 12, 19),
                ),
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DependsExternal(DateRangeTest::class, 'testBasic')]
    #[DataProvider('toDateRangeProvider')]
    public function testToDateRange(DateTimeRange $range, DateRange $expected): void
    {
        $actual = $range->toDateRange();

        self::assertEquals($expected, $actual);
    }

    public static function containsAndIsDuringProvider(): array
    {
        return [
            // LocalDateTimeRange vs LocalDateTime
            'Local.LocalDateTime.0' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                'expected' => true,
            ],
            'Local.LocalDateTime.1' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(20, 15, Time\Second::of(45, 555)),
                ),
                'expected' => true,
            ],
            'Local.LocalDateTime.2' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(23, 59, 59),
                ),
                'expected' => true,
            ],
            'Local.LocalDateTime.3' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                'expected' => false,
            ],
            'Local.LocalDateTime.4' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
                'expected' => false,
            ],
            'Local.LocalDateTime.5' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 14), Time::midday()),
                'expected' => false,
            ],
            'Local.LocalDateTime.6' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(20, 15, 45),
                ),
                'expected' => false,
            ],
            'Local.LocalDateTime.7' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59, 59),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(23, 59, Time\Second::of(59, 1)),
                ),
                'expected' => false,
            ],
            'Local.LocalDateTime.8' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                ),
                'expected' => true,
            ],
            'Local.LocalDateTime.9' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 0, Time\Second::of(0, 1)),
                ),
                'expected' => false,
            ],
            'Local.LocalDateTime.10' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 14),
                    Time::midday(),
                ),
                'expected' => false,
            ],

            // LocalDateTimeRange vs ZonedDateTime
            'Local.ZonedDateTime.0' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Local.ZonedDateTime.1' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(20, 15, Time\Second::of(45, 555)),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Local.ZonedDateTime.2' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(23, 59, 59),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Local.ZonedDateTime.3' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::endOfDay(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Local.ZonedDateTime.4' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Local.ZonedDateTime.5' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 14),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Local.ZonedDateTime.6' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(20, 15, 45),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Local.ZonedDateTime.7' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59, 59),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(23, 59, Time\Second::of(59, 1)),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Local.ZonedDateTime.8' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('Europe/Amsterdam')
                ),
                'expected' => true,
            ],
            'Local.ZonedDateTime.9' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 0, Time\Second::of(0, 1)),
                    new \DateTimeZone('Europe/Amsterdam')
                ),
                'expected' => false,
            ],
            'Local.ZonedDateTime.10' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 14),
                    Time::midday(),
                    new \DateTimeZone('Europe/Amsterdam')
                ),
                'expected' => false,
            ],

            // LocalDateTimeRange vs LocalDateTimeRange
            'Local.LocalRange.0' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59),
                    ),
                ),
                'expected' => true,
            ],
            'Local.LocalRange.1' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(18, 20),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 15, Time\Second::of(45, 555)),
                    ),
                ),
                'expected' => true,
            ],
            'Local.LocalRange.2' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59, 59),
                    ),
                ),
                'expected' => true,
            ],
            'Local.LocalRange.3' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'expected' => false,
            ],
            'Local.LocalRange.4' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midnight(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 59),
                    ),
                ),
                'expected' => false,
            ],
            'Local.LocalRange.5' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::endOfDay(),
                    ),
                ),
                'expected' => false,
            ],
            'Local.LocalRange.6' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::of(12, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(23, 59),
                    ),
                ),
                'expected' => false,
            ],
            'Local.LocalRange.7' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                ),
                'expected' => true,
            ],
            'Local.LocalRange.8' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'expected' => false,
            ],
            'Local.LocalRange.9' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => true,
            ],
            'Local.LocalRange.10' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                ),
                'expected' => false,
            ],

            // ZonedDateTimeRange vs ZonedDateTime
            'Zoned.ZonedDateTime.0' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Zoned.ZonedDateTime.1' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(20, 15, Time\Second::of(45, 555)),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Zoned.ZonedDateTime.2' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(23, 59, 59),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Zoned.ZonedDateTime.3' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::endOfDay(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.4' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.5' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 14),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.6' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(20, 15, 45),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.7' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59, 59),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(23, 59, Time\Second::of(59, 1)),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.8' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => true,
            ],
            'Zoned.ZonedDateTime.9' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('+03:00'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.10' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 0, Time\Second::of(0, 1)),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.11' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 14),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                'expected' => false,
            ],
            'Zoned.ZonedDateTime.12' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'item' => ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(13, 0),
                    new \DateTimeZone('+02:00'),
                ),
                'expected' => true,
            ],

            // ZonedDateTimeRange vs LocalDateTime
            'Zoned.LocalDateTime.0' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                'expected' => true,
            ],
            'Zoned.LocalDateTime.1' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(20, 15, Time\Second::of(45, 555)),
                ),
                'expected' => true,
            ],
            'Zoned.LocalDateTime.2' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(23, 59, 59),
                ),
                'expected' => true,
            ],
            'Zoned.LocalDateTime.3' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.4' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.5' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 14), Time::midday()),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.6' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(20, 15, 45),
                ),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.7' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59, 59),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 16),
                    Time::of(23, 59, Time\Second::of(59, 1)),
                ),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.8' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                ),
                'expected' => true,
            ],
            'Zoned.LocalDateTime.9' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 0, Time\Second::of(0, 1)),
                ),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.10' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 14),
                    Time::midday(),
                ),
                'expected' => false,
            ],
            'Zoned.LocalDateTime.11' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(13, 10),
                ),
                'expected' => true,
            ],
            'Zoned.LocalDateTime.12' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(13, 10),
                ),
                'expected' => true,
            ],
            'Zoned.LocalDateTime.13' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'item' => LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(13, 0),
                ),
                'expected' => false,
            ],

            // ZonedDateTimeRange vs LocalDateTimeRange
            'Zoned.LocalRange.0' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.LocalRange.1' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(18, 20),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 15, Time\Second::of(45, 555)),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.LocalRange.2' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(22, 59, Time\Second::last()),
                        new \DateTimeZone('UTC'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59, 59),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.LocalRange.3' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.LocalRange.4' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midnight(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 59),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.LocalRange.5' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::endOfDay(),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.LocalRange.6' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::of(12, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(23, 59),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.LocalRange.7' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.LocalRange.8' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(0, 59, Time\Second::last()),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.LocalRange.9' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.LocalRange.10' => [
                'range' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'item' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                ),
                'expected' => false,
            ],

            // LocalDateRange vs ZonedDateRange
            'Local.ZonedRange.0' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(0, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Local.ZonedRange.1' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(23, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'expected' => false,
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('containsAndIsDuringProvider')]
    public function testContainsAndIsDuring(DateTimeRange $range, DateTimeRange|DateTime $item, bool $expected): void
    {
        self::assertEquals($expected, $range->contains($item));
        if ($item instanceof DateTimeRange) {
            self::assertEquals($expected, $item->isDuring($range));
        }
    }

    public static function rangesProvider(): array
    {
        return [
            // LocalDateTimeRange vs LocalDateTimeRange
            'Local.Local.intersection.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(1, 10),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
            ],
            'Local.Local.intersection.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                ),
            ],
            'Local.Local.intersection.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                ),
            ],
            'Local.Local.intersection.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
            ],
            'Local.Local.intersection.4' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
            ],
            'Local.Local.intersection.5' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                    ),
                ),
            ],
            'Local.Local.intersection.6' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::midnight(),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
            ],
            'Local.Local.gap.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 30),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
            ],
            'Local.Local.gap.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 00),
                    ),
                ),
            ],
            'Local.Local.gap.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 30),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
            ],
            'Local.Local.gap.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
            ],
            'Local.Local.gap.4' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midnight(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10, Time\Second::of(35, 500_001)),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10, Time\Second::of(35, 600_010)),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10, Time\Second::of(35, 500_001)),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10, Time\Second::of(35, 600_010)),
                    ),
                ),
            ],

            // ZonedDateTimeRange vs ZonedDateTimeRange
            'Zoned.Zoned.intersection.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(1, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.intersection.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.intersection.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.intersection.3' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.intersection.4' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.intersection.5' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+03:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(17, 0),
                        new \DateTimeZone('+04:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.intersection.6' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15, Time\Second::of(0, 123)),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(8, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0, Time\Second::of(15, 999_999)),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15, Time\Second::of(0, 123)),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0, Time\Second::of(15, 999_999)),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 00),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.3' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.4' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 10),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                        new \DateTimeZone('+03:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 0),
                        new \DateTimeZone('+04:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.5' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 10),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 15),
                        new \DateTimeZone('+03:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(19, 0),
                        new \DateTimeZone('+04:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Zoned.gap.6' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 10, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 15, Time\Second::of(10, 999_999)),
                        new \DateTimeZone('+03:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(19, 0),
                        new \DateTimeZone('+04:00'),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 10, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 15, Time\Second::of(10, 999_999)),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],

            // ZonedDateTimeRange vs LocalDateTimeRange
            'Zoned.Local.intersection.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 10),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30, Time\Second::of(15, 123456)),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
            ],
            'Zoned.Local.intersection.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],
            'Zoned.Local.intersection.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15, Time\Second::of(15, 123456)),
                    ),
                ),
                'intersection' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],
            'Zoned.Local.gap.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30, Time\Second::of(15, 123456)),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],
            'Zoned.Local.gap.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30, Time\Second::of(15, 123456)),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],
            'Zoned.Local.gap.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],
            'Zoned.Local.gap.3' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                    ),
                ),
                'gap' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
            ],

            // LocalDateTimeRange vs ZonedDateTimeRange
            'Local.Zoned.intersection.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 0),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                    ),
                ),
            ],
            'Local.Zoned.intersection.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 15),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 15, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15, Time\Second::of(15, 123456)),
                    ),
                ),
            ],
            'Local.Zoned.intersection.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0, Time\Second::of(15, 123456)),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'intersection' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                    ),
                ),
            ],
            'Local.Zoned.gap.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30, Time\Second::of(15, 123456)),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
            ],
            'Local.Zoned.gap.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 0),
                    ),
                ),
            ],
            'Local.Zoned.gap.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
            ],
            'Local.Zoned.gap.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 15),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'gap' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0, Time\Second::of(15, 123456)),
                    ),
                ),
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('rangesProvider')]
    public function testOverlaps(DateTimeRange $a, DateTimeRange $b, ?DateTimeRange $intersection = null, $gap = null): void
    {
        $expected = $intersection !== null;

        self::assertEquals($expected, $a->overlaps($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testIntersection(DateTimeRange $a, DateTimeRange $b, ?DateTimeRange $intersection = null, $gap = null): void
    {
        if ($intersection === null) {
            $this->expectException(NoOverlap::class);
        }
        self::assertEquals($intersection, $a->intersection($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testGap(DateTimeRange $a, DateTimeRange $b, ?DateTimeRange $gap = null, $intersection = null): void
    {
        if ($gap === null) {
            $this->expectException(Overlap::class);
        }
        self::assertEquals($gap, $a->gap($b));
    }

    public static function abutsProvider(): array
    {
        return [
            // LocalDateTimeRange vs LocalDateTimeRange
            'Local.Local.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => true,
            ],
            'Local.Local.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                'expected' => true,
            ],
            'Local.Local.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => true,
            ],
            'Local.Local.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                    ),
                ),
                'expected' => false,
            ],
            'Local.Local.4' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                'expected' => false,
            ],

            // ZonedDateTimeRange vs ZonedDateTimeRange
            'Zoned.Zoned.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.Zoned.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+03:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.Zoned.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.Zoned.3' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(9, 0),
                        new \DateTimeZone('+04:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+04:00'),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.Zoned.4' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => false,
            ],

            // ZonedDateTimeRange vs LocalDateTimeRange
            'Zoned.Local.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.Local.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.Local.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => true,
            ],
            'Zoned.Local.3' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(9, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 59, 59),
                    ),
                ),
                'expected' => false,
            ],
            'Zoned.Local.4' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 0),
                    ),
                ),
                'expected' => false,
            ],

            // LocalDateTimeRange vs ZonedDateTimeRange
            'Local.Zoned.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Local.Zoned.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Local.Zoned.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => true,
            ],
            'Local.Zoned.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(9, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 59, 59),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'expected' => false,
            ],
            'Local.Zoned.4' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 0),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'expected' => false,
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('abutsProvider')]
    public function testAbuts(DateTimeRange $a, DateTimeRange $b, bool $expected): void
    {
        self::assertEquals($expected, $a->abuts($b));
    }

    public static function equalityProvider(): array
    {
        return [
            'Local.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'equals' => true,
            ],
            'Local.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59),
                    ),
                ),
                'equals' => false,
            ],
            'Local.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 0, 1),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'equals' => false,
            ],
            'Zoned.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+03:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'equals' => true,
            ],
            'Zoned.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                'equals' => false,
            ],
            'Zoned.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+02:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'equals' => false,
            ],
            'Mixed.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(0, 59, Time\Second::last()),
                    ),
                ),
                'equals' => true,
            ],
            'Mixed.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'equals' => false,
            ],
            'Mixed.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 0),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                    ),
                ),
                'equals' => false,
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('equalityProvider')]
    public function testEquality(DateTimeRange $a, DateTimeRange $b, bool $equals): void
    {
        self::assertEquals($equals, $a->is($b));
        self::assertNotEquals($equals, $a->isNot($b));

        self::assertEquals($equals, $b->is($a));
        self::assertNotEquals($equals, $b->isNot($a));
    }

    public static function isBeforeProvider(): array
    {
        return [
            'LocalRange.LocalRange.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 10),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                    ),
                ),
                'expected' => true,
            ],
            'LocalRange.LocalRange.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 10),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalRange.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalRange.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalDateTime.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(15, 30),
                ),
                'expected' => true,
            ],
            'LocalRange.LocalDateTime.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(14, 30),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalDateTime.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalDateTime.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(11, 30),
                ),
                'expected' => false,
            ],
            'ZonedRange.ZonedRange.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                'expected' => true,
            ],
            'ZonedRange.ZonedRange.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                'expected' => false,
            ],
            'ZonedRange.ZonedRange.2' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                'expected' => false,
            ],
            'ZonedRange.ZonedDateTime.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(15, 0),
                    new \DateTimeZone('+01:00'),
                ),
                'expected' => true,
            ],
            'ZonedRange.ZonedDateTime.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(14, 0),
                    new \DateTimeZone('+01:00'),
                ),
                'expected' => false,
            ],
            'ZonedRange.LocalRange.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(16, 30),
                    ),
                ),
                'expected' => true,
            ],
            'ZonedRange.LocalRange.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                    ),
                ),
                'expected' => false,
            ],
            'ZonedRange.LocalDateTime.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(15, 0),
                ),
                'expected' => true,
            ],
            'ZonedRange.LocalDateTime.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(14, 0),
                ),
                'expected' => false,
            ],
            'LocalRange.ZonedRange.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(15, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'expected' => true,
            ],
            'LocalRange.ZonedRange.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 0),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.ZonedDateTime.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(14, 0),
                    new \DateTimeZone('+02:00'),
                ),
                'expected' => true,
            ],
            'LocalRange.ZonedDateTime.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(13, 0),
                    new \DateTimeZone('+02:00'),
                ),
                'expected' => false,
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(DateTimeRange $range, DateTimeRange|DateTime $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isBefore($item));
    }

    public static function isAfterProvider(): array
    {
        return [
            'LocalRange.LocalRange.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                    ),
                ),
                'expected' => true,
            ],
            'LocalRange.LocalRange.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalRange.2' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalRange.3' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(14, 15),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.LocalDateTime.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(11, 30),
                ),
                'expected' => true,
            ],
            'LocalRange.LocalDateTime.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 30),
                ),
                'expected' => false,
            ],
            'ZonedRange.ZonedRange.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'expected' => true,
            ],
            'ZonedRange.ZonedRange.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+01:00'),
                    ),
                ),
                'expected' => false,
            ],
            'ZonedRange.ZonedDateTime.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(13, 30),
                    new \DateTimeZone('+04:00'),
                ),
                'expected' => true,
            ],
            'ZonedRange.ZonedDateTime.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(14, 30),
                    new \DateTimeZone('+04:00'),
                ),
                'expected' => false,
            ],
            'ZonedRange.LocalRange.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                    ),
                ),
                'expected' => true,
            ],
            'ZonedRange.LocalRange.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                ),
                'expected' => false,
            ],
            'ZonedRange.LocalDateTime.0' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(11, 30),
                ),
                'expected' => true,
            ],
            'ZonedRange.LocalDateTime.1' => [
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 30),
                        new \DateTimeZone('+00:00'),
                    ),
                ),
                LocalDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 30),
                ),
                'expected' => false,
            ],
            'LocalRange.ZonedRange.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'expected' => true,
            ],
            'LocalRange.ZonedRange.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(10, 30),
                        new \DateTimeZone('+02:00'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                        new \DateTimeZone('+03:00'),
                    ),
                ),
                'expected' => false,
            ],
            'LocalRange.ZonedDateTime.0' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(11, 30),
                    new \DateTimeZone('+02:00'),
                ),
                'expected' => true,
            ],
            'LocalRange.ZonedDateTime.1' => [
                LocalDateTimeRange::of(
                    from: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                    ),
                    to: LocalDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(13, 30),
                    ),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(12, 30),
                    new \DateTimeZone('+03:00'),
                ),
                'expected' => false,
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(DateTimeRange $range, DateTimeRange|DateTime $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isAfter($item));
    }
}
