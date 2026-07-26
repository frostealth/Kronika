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
use Kronika\Range\DateRange;
use Kronika\Range\Exception\InvalidRange;
use Kronika\Range\Exception\NoOverlap;
use Kronika\Range\Exception\Overlap;
use Kronika\Range\ZonedDateTimeRange;
use Kronika\Tests\DateTimeTest;
use Kronika\Tests\DurationTest;
use Kronika\Tests\ZonedDateTimeTest;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZonedDateTimeRange::class)]
final class ZonedDateTimeRangeTest extends TestCase
{
    public static function basicProvider(): array
    {
        return [
            [
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::of(12, 15, 30),
                    new \DateTimeZone('Europe/London'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('America/Los_Angeles'),
                ),
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
                ZonedDateTime::of(
                    Date::of(2026, 2, 15),
                    Time::midnight(),
                    new \DateTimeZone('America/Los_Angeles'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::endOfDay(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
        ];
    }

    #[DependsOnClass(DateTimeTest::class)]
    #[DependsOnClass(ZonedDateTimeTest::class)]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('basicProvider')]
    public function testBasic(ZonedDateTime $from, ZonedDateTime $to): void
    {
        $duration = $from->until($to);
        $range = ZonedDateTimeRange::of($from, $to);

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
                ZonedDateTime::of(
                    Date::of(2026, 1, 5),
                    Time::of(12, 15, 30),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midday(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
            [
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::of(0, 0, Time\Second::of(0, 1)),
                    new \DateTimeZone('Europe/Berlin'),
                ),
                ZonedDateTime::of(
                    Date::of(2025, 12, 15),
                    Time::midnight(),
                    new \DateTimeZone('Europe/Berlin'),
                ),
            ],
        ];
    }

    #[DataProvider('invalidRangeProvider')]
    public function testInvalidRange(ZonedDateTime $from, ZonedDateTime $to): void
    {
        $this->expectException(InvalidRange::class);
        ZonedDateTimeRange::of(from: $from, to: $to);
    }

    #[Depends('testBasic')]
    public function testAround(): void
    {
        $mid = ZonedDateTime::of(
            Date::of(2025, 12, 15),
            Time::endOfDay(),
            new \DateTimeZone('Europe/Berlin'),
        );
        $duration = Duration::ofWeek();
        $from = $mid->sub($duration);
        $to = $mid->add($duration);

        $range = ZonedDateTimeRange::around($mid, $duration);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($from->until($to), $range->duration());
    }

    #[Depends('testBasic')]
    public function testAfter(): void
    {
        $from = ZonedDateTime::of(
            Date::of(2025, 12, 15),
            Time::endOfDay(),
            new \DateTimeZone('Europe/Berlin'),
        );
        $duration = Duration::ofWeek();
        $to = $from->add($duration);

        $range = ZonedDateTimeRange::after($from, $duration);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($from->until($to), $range->duration());
    }

    #[Depends('testBasic')]
    public function testBefore(): void
    {
        $to = ZonedDateTime::of(
            Date::of(2025, 12, 15),
            Time::endOfDay(),
            new \DateTimeZone('Europe/Berlin'),
        );
        $duration = Duration::ofWeek();
        $from = $to->sub($duration);

        $range = ZonedDateTimeRange::before($to, $duration);

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
            'Hours.0' => [
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
            'Hours.1' => [
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
            'Hours.2' => [
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
            'Minutes.0' => [
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
            'Minutes.1' => [
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
            'Seconds.0' => [
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
            'Seconds.1' => [
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
            'Seconds.2' => [
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
            'null.Seconds.0' => [
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
            'Zero.0' => [
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

    #[Depends('testBasic')]
    #[DataProvider('eachProvider')]
    public function testEach(ZonedDateTimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->each($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function splitProvider(): array
    {
        return [
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

    #[Depends('testBasic')]
    #[DataProvider('splitProvider')]
    public function testSplit(ZonedDateTimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->split($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function toDateRangeProvider(): array
    {
        return [
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
                new \DateTimeZone('America/Los_Angeles'),
                DateRange::of(
                    from: Date::of(2025, 12, 15),
                    to: Date::of(2025, 12, 19),
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
                new \DateTimeZone('America/Los_Angeles'),
                DateRange::of(
                    from: Date::of(2025, 12, 15),
                    to: Date::of(2025, 12, 19),
                ),
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toDateRangeProvider')]
    public function testToDateRange(ZonedDateTimeRange $range, \DateTimeZone $timezone, DateRange $expected): void
    {
        $actual = $range->toDateRange($timezone);

        self::assertEquals($expected, $actual);
    }

    public static function containsAndIsDuringProvider(): array
    {
        return [
            // ZonedDateTimeRange vs ZonedDateTimeRange
            'Range.0' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(23, 59),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => true,
            ],
            'Range.1' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(18, 20),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(20, 15, Time\Second::of(45, 555)),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => true,
            ],
            'Range.2' => [
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
                'item' => ZonedDateTimeRange::of(
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
                'expected' => true,
            ],
            'Range.3' => [
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
                'item' => ZonedDateTimeRange::of(
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
                'expected' => false,
            ],
            'Range.4' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midnight(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(11, 59),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => false,
            ],
            'Range.5' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => false,
            ],
            'Range.6' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 14),
                        Time::of(12, 15),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 16),
                        Time::of(23, 59),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => false,
            ],
            'Range.7' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => true,
            ],
            'Range.8' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::endOfDay(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                ),
                'expected' => false,
            ],
            'Range.9' => [
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
                'item' => ZonedDateTimeRange::of(
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
                'expected' => true,
            ],
            'Range.10' => [
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
                'item' => ZonedDateTimeRange::of(
                    from: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::midday(),
                        new \DateTimeZone('Europe/Berlin'),
                    ),
                    to: ZonedDateTime::of(
                        Date::of(2025, 12, 15),
                        Time::of(12, 15),
                        new \DateTimeZone('Europe/Berlin'),
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('containsAndIsDuringProvider')]
    public function testContainsAndIsDuring(
        ZonedDateTimeRange $range,
        ZonedDateTimeRange|ZonedDateTime $item,
        bool $expected,
    ): void {
        self::assertEquals($expected, $range->contains($item));
        if ($item instanceof ZonedDateTimeRange) {
            self::assertEquals($expected, $item->isDuring($range));
        }
    }

    public static function rangesProvider(): array
    {
        return [
            'intersection.0' => [
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
            'intersection.1' => [
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
            'intersection.2' => [
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
            'intersection.3' => [
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
            'intersection.4' => [
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
            'intersection.5' => [
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
            'intersection.6' => [
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
            'gap.0' => [
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
            'gap.1' => [
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
            'gap.2' => [
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
            'gap.3' => [
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
            'gap.4' => [
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
            'gap.5' => [
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
            'gap.6' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('rangesProvider')]
    public function testOverlaps(
        ZonedDateTimeRange $a,
        ZonedDateTimeRange $b,
        ?ZonedDateTimeRange $intersection = null,
        $gap = null,
    ): void {
        $expected = $intersection !== null;

        self::assertEquals($expected, $a->overlaps($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testIntersection(
        ZonedDateTimeRange $a,
        ZonedDateTimeRange $b,
        ?ZonedDateTimeRange $intersection = null,
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
        ZonedDateTimeRange $a,
        ZonedDateTimeRange $b,
        ?ZonedDateTimeRange $gap = null,
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
            '0' => [
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
            '1' => [
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
            '2' => [
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
            '3' => [
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
            '4' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('abutsProvider')]
    public function testAbuts(
        ZonedDateTimeRange $a,
        ZonedDateTimeRange $b,
        bool $expected,
    ): void {
        self::assertEquals($expected, $a->abuts($b));
    }

    public static function equalityProvider(): array
    {
        return [
            '0' => [
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
            '1' => [
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
            '2' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('equalityProvider')]
    public function testEquality(
        ZonedDateTimeRange $a,
        ZonedDateTimeRange $b,
        bool $equals,
    ): void {
        self::assertEquals($equals, $a->is($b));
        self::assertNotEquals($equals, $a->isNot($b));

        self::assertEquals($equals, $b->is($a));
        self::assertNotEquals($equals, $b->isNot($a));
    }

    public static function isBeforeProvider(): array
    {
        return [
            'Range.Range.0' => [
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
            'Range.Range.1' => [
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
            'Range.Range.2' => [
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
            'Range.ZonedDateTime.0' => [
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
            'Range.ZonedDateTime.1' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(
        ZonedDateTimeRange $range,
        ZonedDateTimeRange|ZonedDateTime $item,
        bool $expected,
    ): void {
        self::assertEquals($expected, $range->isBefore($item));
    }

    public static function isAfterProvider(): array
    {
        return [
            'Range.Range.0' => [
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
            'Range.Range.1' => [
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
            'Range.ZonedDateTime.0' => [
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
            'Range.ZonedDateTime.1' => [
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
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(
        ZonedDateTimeRange $range,
        ZonedDateTimeRange|ZonedDateTime $item,
        bool $expected,
    ): void {
        self::assertEquals($expected, $range->isAfter($item));
    }
}
