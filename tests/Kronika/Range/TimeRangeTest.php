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

use Kronika\Duration;
use Kronika\Range\Exception\NoOverlap;
use Kronika\Range\Exception\Overlap;
use Kronika\Range\TimeRange;
use Kronika\Tests\TimeTest;
use Kronika\Time;
use Kronika\Time\Second;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeRange::class)]
final class TimeRangeTest extends TestCase
{
    #[DependsOnClass(TimeTest::class)]
    public function testBasic(): void
    {
        $from = Time::of(12, 35, Second::of(45, 123));
        $to = Time::of(21, 50, Second::of(45, 999));
        $duration = $from->until($to);
        $range = TimeRange::of($from, $to);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($duration, $range->duration());
        self::assertEquals($duration->isZero(), $range->isZero());
    }

    public static function eachProvider(): array
    {
        return [
            'Hours.0' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(50)),
                ),
                Duration::ofHour(),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(13, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(45, 123)),
                ],
            ],
            'Hours.1' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(45, 110)),
                ),
                Duration::ofHour(),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(13, 35, Second::of(45, 123)),
                ],
            ],
            'Hours.2' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(50)),
                ),
                Duration::of(hours: 2),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(45, 123)),
                ],
            ],
            'Hours.3' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(45)),
                ),
                Duration::of(hours: 2),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Hours.4' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(45)),
                ),
                Duration::of(hours: 4),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Hours.5' => [
                TimeRange::of(
                    Time::midnight(),
                    Time::endOfDay(),
                ),
                Duration::of(hours: 12),
                [
                    Time::midnight(),
                    Time::midday(),
                ],
            ],
            'Minutes.0' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(50)),
                ),
                Duration::of(minutes: 30),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(13, 05, Second::of(45, 123)),
                    Time::of(13, 35, Second::of(45, 123)),
                    Time::of(14, 05, Second::of(45, 123)),
                    Time::of(14, 35, Second::of(45, 123)),
                ],
            ],
            'Minutes.1' => [
                TimeRange::of(
                    Time::of(12, 35, 45),
                    Time::of(12, 55, 50),
                ),
                Duration::of(minutes: 5),
                [
                    Time::of(12, 35, 45),
                    Time::of(12, 40, 45),
                    Time::of(12, 45, 45),
                    Time::of(12, 50, 45),
                    Time::of(12, 55, 45),
                ],
            ],
            'Minutes.2' => [
                TimeRange::of(
                    Time::of(12, 35, 45),
                    Time::of(12, 35, 45),
                ),
                Duration::of(minutes: 1),
                [
                    Time::of(12, 35, 45),
                ],
            ],
            'Seconds.0' => [
                TimeRange::of(
                    Time::of(12, 35, 45),
                    Time::of(12, 35, 55),
                ),
                Duration::of(seconds: 5),
                [
                    Time::of(12, 35, 45),
                    Time::of(12, 35, 50),
                ],
            ],
            'Seconds.1' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(50, 110)),
                ),
                Duration::of(seconds: 1),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(46, 123)),
                    Time::of(12, 35, Second::of(47, 123)),
                    Time::of(12, 35, Second::of(48, 123)),
                    Time::of(12, 35, Second::of(49, 123)),
                ],
            ],
            'Seconds.2' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(45, 123)),
                ),
                Duration::of(seconds: 1),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Seconds.3' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    to: null,
                ),
                Duration::of(seconds: 1),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Zero' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(47, 110)),
                ),
                Duration::zero(),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Days.0' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(47, 110)),
                ),
                Duration::of(days: 2),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Days.1' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(45, 123)),
                ),
                Duration::of(days: 1),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('eachProvider')]
    public function testEach(TimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->each($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function splitProvider(): array
    {
        return [
            'Seconds.0' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(12, 0, Second::of(3, 123)),
                ),
                'step' => Duration::ofSecond(),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(12, 0, 1),
                    ),
                    TimeRange::of(
                        Time::of(12, 0, 1),
                        Time::of(12, 0, 2),
                    ),
                    TimeRange::of(
                        Time::of(12, 0, 2),
                        Time::of(12, 0, 3),
                    ),
                    TimeRange::of(
                        Time::of(12, 0, 3),
                        Time::of(12, 0, Second::of(3, 123)),
                    ),
                ],
            ],
            'Seconds.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(12, 0, Second::of(3, 123)),
                ),
                'step' => Duration::of(seconds: 2),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(12, 0, 2),
                    ),
                    TimeRange::of(
                        Time::of(12, 0, 2),
                        Time::of(12, 0, Second::of(3, 123)),
                    ),
                ],
            ],
            'Seconds.2' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(12, 0, Second::of(3, 123)),
                ),
                'step' => Duration::of(seconds: 4),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(12, 0, Second::of(3, 123)),
                    ),
                ],
            ],
            'Minutes.0' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(12, 0, Second::of(3, 123)),
                ),
                'step' => Duration::ofMinute(),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(12, 0, Second::of(3, 123)),
                    ),
                ],
            ],
            'Minutes.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(12, 2, Second::of(2, 123)),
                ),
                'step' => Duration::ofMinute(),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(12, 1),
                    ),
                    TimeRange::of(
                        Time::of(12, 1),
                        Time::of(12, 2),
                    ),
                    TimeRange::of(
                        Time::of(12, 2),
                        Time::of(12, 2, Second::of(2, 123)),
                    ),
                ],
            ],
            'Minutes.2' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::midday(),
                ),
                'step' => Duration::ofMinute(),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::midday(),
                    ),
                ],
            ],
            'Hours.0' => [
                'range' => TimeRange::of(
                    Time::of(22, 0),
                    Time::endOfDay(),
                ),
                'step' => Duration::ofHour(),
                'expected' => [
                    TimeRange::of(
                        Time::of(22, 0),
                        Time::of(23, 0),
                    ),
                    TimeRange::of(
                        Time::of(23, 0),
                        Time::endOfDay(),
                    ),
                ],
            ],
            'Hours.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::endOfDay(),
                ),
                'step' => Duration::of(hours: 6),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(18, 0),
                    ),
                    TimeRange::of(
                        Time::of(18, 0),
                        Time::endOfDay(),
                    ),
                ],
            ],
            'Hours.2' => [
                'range' => TimeRange::of(
                    Time::midnight(),
                    Time::endOfDay(),
                ),
                'step' => Duration::of(hours: 12),
                'expected' => [
                    TimeRange::of(
                        Time::midnight(),
                        Time::midday(),
                    ),
                    TimeRange::of(
                        Time::midday(),
                        Time::endOfDay(),
                    ),
                ],
            ],
            'Days.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(13, 2, Second::of(2, 123)),
                ),
                'step' => Duration::of(days: 2, hours: 1),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(13, 2, Second::of(2, 123)),
                    ),
                ],
            ],
            'Zero.0' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(13, 2, Second::of(2, 123)),
                ),
                'step' => Duration::zero(),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::of(13, 2, Second::of(2, 123)),
                    ),
                ],
            ],
            'Zero.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::midday(),
                ),
                'step' => Duration::zero(),
                'expected' => [
                    TimeRange::of(
                        Time::midday(),
                        Time::midday(),
                    ),
                ],
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('splitProvider')]
    public function testSplit(TimeRange $range, Duration $step, array $expected): void
    {
        $actual = $range->split($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function containsProvider(): array
    {
        return [
            'Range.Range.0' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::midday(),
                    Time::of(13, 0),
                ),
                'contains' => true,
            ],
            'Range.Range.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::midday(),
                    Time::midday(),
                ),
                'contains' => true,
            ],
            'Range.Range.2' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, 35),
                ),
                'contains' => true,
            ],
            'Range.Range.3' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'contains' => true,
            ],
            'Range.Range.4' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::of(14, 25, Second::of(35, 123)),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'contains' => false,
            ],
            'Range.Range.5' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::of(11, 25, Second::of(35, 123)),
                    Time::midday(),
                ),
                'contains' => false,
            ],
            'Range.Range.6' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => TimeRange::of(
                    Time::of(11, 25, Second::of(35, 123)),
                    Time::of(11, 30),
                ),
                'contains' => false,
            ],
            'Range.Time.0' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => Time::of(12, 25, Second::of(35, 123)),
                'contains' => true,
            ],
            'Range.Time.1' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => Time::midday(),
                'contains' => true,
            ],
            'Range.Time.2' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => Time::of(14, 25, Second::of(35, 0)),
                'contains' => true,
            ],
            'Range.Time.3' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => Time::of(14, 25, Second::of(35, 123)),
                'contains' => false,
            ],
            'Range.Time.4' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => Time::endOfDay(),
                'contains' => false,
            ],
            'Range.Time.5' => [
                'range' => TimeRange::of(
                    Time::midday(),
                    Time::of(14, 25, Second::of(35, 123)),
                ),
                'item' => Time::midnight(),
                'contains' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('containsProvider')]
    public function testContainsAndIsDuring(TimeRange $range, TimeRange|Time $item, bool $contains): void
    {
        self::assertEquals($contains, $range->contains($item));
        if ($item instanceof TimeRange) {
            self::assertEquals($contains, $item->isDuring($range));
        }
    }

    public static function rangesProvider(): array
    {
        return [
            'intersection.0' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(11, 30, 45),
                    to: Time::of(14, 25),
                ),
                'intersection' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25),
                ),
            ],
            'intersection.1' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(13, 30, 45),
                    to: Time::of(15, 0),
                ),
                'intersection' => TimeRange::of(
                    from: Time::of(13, 30, 45),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
            ],
            'intersection.2' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(13, 30, 45),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                'intersection' => TimeRange::of(
                    from: Time::of(13, 30, 45),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
            ],
            'intersection.3' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(13, 30, 45),
                ),
                'intersection' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(13, 30, 45),
                ),
            ],
            'intersection.4' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                'intersection' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
            ],
            'intersection.5' => [
                TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 0),
                ),
                'intersection' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 0),
                ),
            ],
            'intersection.6' => [
                TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                'intersection' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
            ],
            'gap.0' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(10, 30, 45),
                    to: Time::of(11, 15, Second::of(45, 987)),
                ),
                'gap' => TimeRange::of(
                    from: Time::of(11, 15, Second::of(45, 987)),
                    to: Time::midday(),
                ),
            ],
            'gap.1' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(10, 30, 45),
                    to: Time::midday(),
                ),
                'gap' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::midday(),
                ),
            ],
            'gap.2' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(14, 25, Second::of(35, 123)),
                    to: Time::of(16, 45),
                ),
                'gap' => TimeRange::of(
                    from: Time::of(14, 25, Second::of(35, 123)),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
            ],
            'gap.3' => [
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 25, Second::of(35, 123)),
                ),
                TimeRange::of(
                    from: Time::of(14, 30, Second::of(10, 987)),
                    to: Time::of(16, 45),
                ),
                'gap' => TimeRange::of(
                    from: Time::of(14, 25, Second::of(35, 123)),
                    to: Time::of(14, 30, Second::of(10, 987)),
                ),
            ],
            'gap.4' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midnight(),
                ),
                'gap' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midnight(),
                ),
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('rangesProvider')]
    public function testOverlaps(TimeRange $a, TimeRange $b, ?TimeRange $intersection = null, $gap = null): void
    {
        $expected = $intersection !== null;

        self::assertEquals($expected, $a->overlaps($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testIntersection(TimeRange $a, TimeRange $b, ?TimeRange $intersection = null, $gap = null): void
    {
        if ($intersection === null) {
            $this->expectException(NoOverlap::class);
        }
        self::assertEquals($intersection, $a->intersection($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testGap(TimeRange $a, TimeRange $b, ?TimeRange $gap = null, $intersection = null): void
    {
        if ($gap === null) {
            $this->expectException(Overlap::class);
        }
        self::assertEquals($gap, $a->gap($b));
    }

    public static function abutsProvider(): array
    {
        return [
            'Abuts.0' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'abuts' => true,
            ],
            'Abuts.1' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::midday(),
                ),
                'abuts' => true,
            ],
            'Abuts.2' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midnight(),
                ),
                'abuts' => true,
            ],
            'DoesNotAbut.0' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                TimeRange::of(
                    from: Time::of(13, 0),
                    to: Time::endOfDay(),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.1' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                TimeRange::of(
                    from: Time::of(11, 59),
                    to: Time::endOfDay(),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.2' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.3' => [
                TimeRange::of(
                    from: Time::of(12, 0, Second::of(0, 123)),
                    to: Time::endOfDay(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'abuts' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('abutsProvider')]
    public function testAbuts(TimeRange $a, TimeRange $b, bool $abuts): void
    {
        self::assertEquals($abuts, $a->abuts($b));
        self::assertEquals($abuts, $b->abuts($a));
    }

    public static function equalityProvider(): array
    {
        return [
            'Equal.0' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'equals' => true,
            ],
            'Equal.1' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                'equals' => true,
            ],
            'NotEqual.0' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'equals' => false,
            ],
            'NotEqual.1' => [
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::endOfDay(),
                ),
                TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'equals' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('equalityProvider')]
    public function testEquality(TimeRange $a, TimeRange $b, bool $equals): void
    {
        self::assertEquals($equals, $a->is($b));
        self::assertNotEquals($equals, $a->isNot($b));
    }

    public static function isBeforeProvider(): array
    {
        return [
            'Range.0' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(12, 0, Second::of(0, 123)),
                    to: Time::endOfDay(),
                ),
                'expected' => true,
            ],
            'Range.1' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'expected' => false,
            ],
            'Range.2' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::of(13, 0),
                ),
                'expected' => false,
            ],
            'Range.3' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::midday(),
                ),
                'expected' => false,
            ],
            'Range.4' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::of(11, 45),
                ),
                'expected' => false,
            ],
            'Range.5' => [
                'range' => TimeRange::of(
                    from: Time::of(10, 30),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(10, 30),
                    to: Time::of(11, 45),
                ),
                'expected' => false,
            ],
            'Range.6' => [
                'range' => TimeRange::of(
                    from: Time::of(10, 30),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(10, 30),
                    to: Time::of(10, 30),
                ),
                'expected' => false,
            ],
            'Range.7' => [
                'range' => TimeRange::of(
                    from: Time::of(10, 30),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::of(10, 0),
                    to: Time::of(10, 15),
                ),
                'expected' => false,
            ],
            'Time.0' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => Time::of(12, 0, Second::of(0, 123)),
                'expected' => true,
            ],
            'Time.1' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => Time::midday(),
                'expected' => false,
            ],
            'Time.2' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'item' => Time::of(11, 30),
                'expected' => false,
            ],
            'Time.3' => [
                'range' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::midday(),
                ),
                'item' => Time::of(11, 30),
                'expected' => false,
            ],
            'Time.4' => [
                'range' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::midday(),
                ),
                'item' => Time::midnight(),
                'expected' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(TimeRange $range, TimeRange|Time $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isBefore($item));
    }

    public static function isAfterProvider(): array
    {
        return [
            'Range.0' => [
                'range' => TimeRange::of(
                    from: Time::of(12, 0, Second::of(0, 123)),
                    to: Time::endOfDay(),
                ),
                'item' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'expected' => true,
            ],
            'Range.1' => [
                'range' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'item' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'expected' => false,
            ],
            'Range.2' => [
                'range' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::midday(),
                ),
                'item' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'expected' => false,
            ],
            'Range.3' => [
                'range' => TimeRange::of(
                    from: Time::of(10, 30),
                    to: Time::of(11, 30),
                ),
                'item' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::midday(),
                ),
                'expected' => false,
            ],
            'Range.4' => [
                'range' => TimeRange::of(
                    from: Time::midnight(),
                    to: Time::of(10, 30),
                ),
                'item' => TimeRange::of(
                    from: Time::of(11, 30),
                    to: Time::midday(),
                ),
                'expected' => false,
            ],
            'Time.0' => [
                'range' => TimeRange::of(
                    from: Time::of(12, 0, Second::of(0, 123)),
                    to: Time::endOfDay(),
                ),
                'item' => Time::midday(),
                'expected' => true,
            ],
            'Time.1' => [
                'range' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'item' => Time::midday(),
                'expected' => false,
            ],
            'Time.2' => [
                'range' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'item' => Time::endOfDay(),
                'expected' => false,
            ],
            'Time.3' => [
                'range' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::endOfDay(),
                ),
                'item' => Time::midnight(),
                'expected' => true,
            ],
            'Time.4' => [
                'range' => TimeRange::of(
                    from: Time::midday(),
                    to: Time::of(14, 30),
                ),
                'item' => Time::of(20, 15),
                'expected' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(TimeRange $range, TimeRange|Time $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isAfter($item));
    }
}
