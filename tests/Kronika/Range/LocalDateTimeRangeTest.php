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
use Kronika\Duration;
use Kronika\LocalDateTime;
use Kronika\Range\DateRange;
use Kronika\Range\Exception\InvalidRange;
use Kronika\Range\Exception\NoOverlap;
use Kronika\Range\Exception\Overlap;
use Kronika\Range\LocalDateTimeRange;
use Kronika\Range\ZonedDateTimeRange;
use Kronika\Tests\DateTimeTest;
use Kronika\Tests\DurationTest;
use Kronika\Tests\LocalDateTimeTest;
use Kronika\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalDateTimeRange::class)]
final class LocalDateTimeRangeTest extends TestCase
{
    public static function basicProvider(): array
    {
        return [
            [
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
                LocalDateTime::of(Date::of(2026, 1, 5), Time::of(12, 15, 30)),
            ],
            [
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                LocalDateTime::of(Date::of(2026, 1, 5), Time::of(12, 15, 30)),
            ],
            [
                LocalDateTime::of(Date::of(2026, 1, 5), Time::of(12, 15, 30)),
                LocalDateTime::of(Date::of(2026, 2, 15), Time::midnight()),
            ],
            [
                LocalDateTime::of(Date::of(2026, 1, 5), Time::midday()),
                LocalDateTime::of(Date::of(2026, 1, 5), Time::midday()),
            ],
            [
                LocalDateTime::of(Date::of(2026, 1, 5), Time::midday()),
                LocalDateTime::of(Date::of(2026, 1, 5), Time::endOfDay()),
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DependsOnClass(LocalDateTimeTest::class)]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('basicProvider')]
    public function testBasic(LocalDateTime $from, LocalDateTime $to): void
    {
        $duration = $from->until($to);
        $range = LocalDateTimeRange::of($from, $to);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertEquals($from->is($to), $range->contains($to));
        self::assertEquals($duration, $range->duration());
        self::assertEquals($duration->isZero(), $range->isZero());
    }

    public static function invalidRangeProvider(): array
    {
        return [
            [
                LocalDateTime::of(Date::of(2026, 1, 5), Time::of(12, 15, 30)),
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
            ],
            [
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
            ],
            [
                LocalDateTime::of(Date::of(2025, 12, 15), Time::of(0, 0, Time\Second::of(0, 1))),
                LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
            ],
        ];
    }

    #[DataProvider('invalidRangeProvider')]
    public function testInvalidRange(LocalDateTime $from, LocalDateTime $to): void
    {
        $this->expectException(InvalidRange::class);
        LocalDateTimeRange::of(from: $from, to: $to);
    }

    #[Depends('testBasic')]
    public function testAround(): void
    {
        $mid = LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay());
        $duration = Duration::ofWeek();
        $from = $mid->sub($duration);
        $to = $mid->add($duration);

        $range = LocalDateTimeRange::around($mid, $duration);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($from->until($to), $range->duration());
    }

    #[Depends('testBasic')]
    public function testAfter(): void
    {
        $from = LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay());
        $duration = Duration::ofWeek();
        $to = $from->add($duration);

        $range = LocalDateTimeRange::after($from, $duration);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($from->until($to), $range->duration());
    }

    #[Depends('testBasic')]
    public function testBefore(): void
    {
        $to = LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay());
        $duration = Duration::ofWeek();
        $from = $to->sub($duration);

        $range = LocalDateTimeRange::before($to, $duration);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($from->until($to), $range->duration());
    }

    public static function eachProvider(): array
    {
        return [
            'Days.0' => [
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
            'Hours.0' => [
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
            'Hours.1' => [
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
            'Hours.2' => [
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
            'Minutes.0' => [
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
            'Minutes.1' => [
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
            'Seconds.0' => [
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
            'Seconds.1' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
            'null.Seconds.0' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    to: null,
                ),
                Duration::of(seconds: 1),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
            'Zero.0' => [
                LocalDateTimeRange::of(
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 25)),
                ),
                Duration::zero(),
                [
                    LocalDateTime::of(Date::of(2025, 12, 28), Time::of(10, 15, 20)),
                ],
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('eachProvider')]
    public function testEach(LocalDateTimeRange $range, Duration $step, array $expected): void
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('splitProvider')]
    public function testSplit(LocalDateTimeRange $range, Duration $step, array $expected): void
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toDateRangeProvider')]
    public function testToDateRange(LocalDateTimeRange $range, DateRange $expected): void
    {
        $actual = $range->toDateRange();

        self::assertEquals($expected, $actual);
    }

    public static function containsAndIsDuringProvider(): array
    {
        return [
            // LocalDateTimeRange vs LocalDateTime
            'LocalDateTime.0' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                'expected' => true,
            ],
            'LocalDateTime.1' => [
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
            'LocalDateTime.2' => [
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
            'LocalDateTime.3' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                'expected' => false,
            ],
            'LocalDateTime.4' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 15), Time::midnight()),
                'expected' => false,
            ],
            'LocalDateTime.5' => [
                'range' => LocalDateTimeRange::of(
                    from: LocalDateTime::of(Date::of(2025, 12, 15), Time::midday()),
                    to: LocalDateTime::of(Date::of(2025, 12, 15), Time::endOfDay()),
                ),
                'item' => LocalDateTime::of(Date::of(2025, 12, 14), Time::midday()),
                'expected' => false,
            ],
            'LocalDateTime.6' => [
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
            'LocalDateTime.7' => [
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
            'LocalDateTime.8' => [
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
            'LocalDateTime.9' => [
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
            'LocalDateTime.10' => [
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

            // LocalDateTimeRange vs LocalDateTimeRange
            'Range.0' => [
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
            'Range.1' => [
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
            'Range.2' => [
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
            'Range.3' => [
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
            'Range.4' => [
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
            'Range.5' => [
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
            'Range.6' => [
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
            'Range.7' => [
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
            'Range.8' => [
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
            'Range.9' => [
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
            'Range.10' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('containsAndIsDuringProvider')]
    public function testContainsAndIsDuring(
        LocalDateTimeRange $range,
        LocalDateTimeRange|LocalDateTime $item,
        bool $expected,
    ): void {
        self::assertEquals($expected, $range->contains($item));
        if ($item instanceof LocalDateTimeRange) {
            self::assertEquals($expected, $item->isDuring($range));
        }
    }

    public static function rangesProvider(): array
    {
        return [
            'intersection.0' => [
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
            'intersection.1' => [
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
            'intersection.2' => [
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
            'intersection.3' => [
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
            'intersection.4' => [
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
            'intersection.5' => [
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
            'intersection.6' => [
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
            'gap.0' => [
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
            'gap.1' => [
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
            'gap.2' => [
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
            'gap.3' => [
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
            'gap.4' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('rangesProvider')]
    public function testOverlaps(
        LocalDateTimeRange $a,
        LocalDateTimeRange $b,
        ?LocalDateTimeRange $intersection = null,
        $gap = null,
    ): void {
        $expected = $intersection !== null;

        self::assertEquals($expected, $a->overlaps($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testIntersection(
        LocalDateTimeRange $a,
        LocalDateTimeRange $b,
        ?LocalDateTimeRange $intersection = null,
        $gap = null,
    ): void {
        if ($intersection === null) {
            $this->expectException(NoOverlap::class);
        }
        self::assertEquals($intersection, $a->intersection($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testGap(
        LocalDateTimeRange $a,
        LocalDateTimeRange $b,
        ?LocalDateTimeRange $gap = null,
        $intersection = null,
    ): void {
        if ($gap === null) {
            $this->expectException(Overlap::class);
        }
        self::assertEquals($gap, $a->gap($b));
    }

    public static function abutsProvider(): array
    {
        return [
            // LocalDateTimeRange vs LocalDateTimeRange
            '0' => [
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
            '1' => [
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
            '2' => [
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
            '3' => [
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
            '4' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('abutsProvider')]
    public function testAbuts(LocalDateTimeRange $a, LocalDateTimeRange $b, bool $expected): void
    {
        self::assertEquals($expected, $a->abuts($b));
    }

    public static function equalityProvider(): array
    {
        return [
            '0' => [
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
            '1' => [
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
            '2' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('equalityProvider')]
    public function testEquality(LocalDateTimeRange $a, LocalDateTimeRange $b, bool $equals): void
    {
        self::assertEquals($equals, $a->is($b));
        self::assertNotEquals($equals, $a->isNot($b));

        self::assertEquals($equals, $b->is($a));
        self::assertNotEquals($equals, $b->isNot($a));
    }

    public static function isBeforeProvider(): array
    {
        return [
            'Range.Range.0' => [
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
            'Range.Range.1' => [
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
            'Range.Range.2' => [
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
            'Range.Range.3' => [
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
            'Range.LocalDateTime.0' => [
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
            'Range.LocalDateTime.1' => [
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
            'Range.LocalDateTime.2' => [
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
            'Range.LocalDateTime.3' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(LocalDateTimeRange $range, LocalDateTimeRange|LocalDateTime $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isBefore($item));
    }

    public static function isAfterProvider(): array
    {
        return [
            'Range.Range.0' => [
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
            'Range.Range.1' => [
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
            'Range.Range.2' => [
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
            'Range.Range.3' => [
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
            'Range.LocalDateTime.0' => [
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
            'Range.LocalDateTime.1' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(LocalDateTimeRange $range, LocalDateTimeRange|LocalDateTime $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isAfter($item));
    }

    #[DependsExternal(ZonedDateTimeRangeTest::class, 'testBasic')]
    public function testAt(): void
    {
        $timezone = new \DateTimeZone('Europe/Amsterdam');
        $from = LocalDateTime::of(Date::of(2025, 12, 15), Time::midday());
        $to = LocalDateTime::of(Date::of(2025, 12, 16), Time::endOfDay());
        $range = LocalDateTimeRange::of($from, $to);

        $actual = $range->in($timezone);

        self::assertEquals(ZonedDateTimeRange::of($from->in($timezone), $to->in($timezone)), $actual);
    }
}
