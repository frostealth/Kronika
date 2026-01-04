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
use Kronika\Tests\DateTest;
use Kronika\Tests\DurationTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateRange::class)]
final class DateRangeTest extends TestCase
{
    #[DependsOnClass(DateTest::class)]
    #[DependsOnClass(DurationTest::class)]
    public function testBasic(): void
    {
        $from = Date::of(2025, 12, 15);
        $to = Date::of(2026, 1, 1);
        $duration = $from->until($to);

        $range = DateRange::of($from, $to);

        self::assertEquals($from, $range->from());
        self::assertEquals($to, $range->to());
        self::assertTrue($range->contains($from));
        self::assertFalse($range->contains($to));
        self::assertEquals($duration, $range->duration());
        self::assertEquals($duration->isZero(), $range->isZero());
    }

    public function testInvalidRange(): void
    {
        $this->expectException(InvalidRange::class);
        DateRange::of(
            from: Date::of(2025, 12, 15),
            to: Date::of(2025, 12, 14),
        );
    }

    #[Depends('testBasic')]
    public function testAround(): void
    {
        $mid = Date::of(2025, 12, 15);
        $duration = Duration::ofWeek();
        $from = $mid->sub($duration);
        $to = $mid->add($duration);

        $range = DateRange::around($mid, $duration);

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
                DateRange::of(Date::of(2025, 12, 28), Date::of(2026, 1, 1)),
                Duration::ofDay(),
                [
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 29),
                    Date::of(2025, 12, 30),
                    Date::of(2025, 12, 31),
                ],
            ],
            'Days.1' => [
                DateRange::of(Date::of(2025, 12, 26), Date::of(2026, 1, 2)),
                Duration::of(days: 2),
                [
                    Date::of(2025, 12, 26),
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 30),
                    Date::of(2026, 1, 1),
                ],
            ],
            'Days.2' => [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 28)),
                Duration::ofDay(),
                [
                    Date::of(2025, 12, 28),
                ],
            ],
            'Days.3' => [
                DateRange::of(Date::of(2025, 12, 28), to: null),
                Duration::ofDay(),
                [
                    Date::of(2025, 12, 28),
                ],
            ],
            'Days.4' => [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 30)),
                Duration::of(days: 4),
                [
                    Date::of(2025, 12, 28),
                ],
            ],
            'Hours.0' => [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 31)),
                Duration::of(hours: 2),
                [
                    Date::of(2025, 12, 28),
                ],
            ],
            'Hours.1' => [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 31)),
                Duration::of(hours: 50),
                [
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 30),
                ],
            ],
            'Zero' => [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 31)),
                Duration::zero(),
                [
                    Date::of(2025, 12, 28),
                ],
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('eachProvider')]
    public function testEach(DateRange $range, Duration $step, array $expected): void
    {
        $actual = $range->each($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function splitProvider(): array
    {
        return [
            'Days.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'step' => Duration::ofDay(),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 29),
                    ),
                    DateRange::of(
                        from: Date::of(2025, 12, 29),
                        to: Date::of(2025, 12, 30),
                    ),
                ],
            ],
            'Days.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'step' => Duration::of(days: 2),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 30),
                    ),
                ],
            ],
            'Days.3' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'step' => Duration::of(days: 3),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 30),
                    ),
                ],
            ],
            'Days.4' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                'step' => Duration::of(days: 2),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 30),
                    ),
                    DateRange::of(
                        from: Date::of(2025, 12, 30),
                        to: Date::of(2025, 12, 31),
                    ),
                ],
            ],
            'Hours.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                'step' => Duration::ofHour(),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 31),
                    ),
                ],
            ],
            'Hours.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                'step' => Duration::of(hours: 50),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 30),
                    ),
                    DateRange::of(
                        from: Date::of(2025, 12, 30),
                        to: Date::of(2025, 12, 31),
                    ),
                ],
            ],
            'Zero' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                'step' => Duration::zero(),
                'expected' => [
                    DateRange::of(
                        from: Date::of(2025, 12, 28),
                        to: Date::of(2025, 12, 31),
                    ),
                ],
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('splitProvider')]
    public function testSplit(DateRange $range, Duration $step, array $expected): void
    {
        $actual = $range->split($step);

        self::assertIsIterable($actual);
        self::assertEquals($expected, \iterator_to_array($actual));
    }

    public static function containsProvider(): array
    {
        return [
            'Range.Range.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 30),
                ),
                'contains' => true,
            ],
            'Range.Range.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 29),
                ),
                'contains' => true,
            ],
            'Range.Range.2' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 28),
                ),
                'contains' => true,
            ],
            'Range.Range.3' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2025, 12, 30),
                ),
                'contains' => false,
            ],
            'Range.Range.4' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'contains' => false,
            ],
            'Range.Range.5' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 31),
                ),
                'contains' => false,
            ],
            'Range.Range.6' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 27),
                    to: Date::of(2025, 12, 29),
                ),
                'contains' => false,
            ],
            'Range.Range.7' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 27),
                ),
                'contains' => false,
            ],
            'Range.Date.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 29),
                'contains' => true,
            ],
            'Range.Date.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 28),
                'contains' => true,
            ],
            'Range.Date.2' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 30),
                'contains' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('containsProvider')]
    public function testContainsAndIsDuring(DateRange $range, DateRange|Date $item, bool $contains): void
    {
        self::assertEquals($contains, $range->contains($item));
        if ($item instanceof DateRange) {
            self::assertEquals($contains, $item->isDuring($range));
        }
    }

    public static function rangesProvider(): array
    {
        return [
            'intersection.0' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 31),
                ),
                'intersection' => DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 30),
                ),
            ],
            'intersection.1' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 30),
                ),
                'intersection' => DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 30),
                ),
            ],
            'intersection.2' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 27),
                    to: Date::of(2025, 12, 29),
                ),
                'intersection' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 29),
                ),
            ],
            'intersection.3' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2026, 1, 1),
                ),
                'intersection' => DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2025, 12, 31),
                ),
            ],
            'intersection.4' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 31),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 27),
                    to: Date::of(2025, 12, 29),
                ),
                'intersection' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 29),
                ),
            ],
            'gap.0' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 31),
                    to: Date::of(2026, 1, 1),
                ),
                'gap' => DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2025, 12, 31),
                ),
            ],
            'gap.1' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2026, 1, 1),
                ),
                'gap' => DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2025, 12, 30),
                ),
            ],
            'gap.2' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 26),
                    to: Date::of(2025, 12, 27),
                ),
                'gap' => DateRange::of(
                    from: Date::of(2025, 12, 27),
                    to: Date::of(2025, 12, 28),
                ),
            ],
            'gap.3' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 26),
                    to: Date::of(2025, 12, 28),
                ),
                'gap' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 28),
                ),
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('rangesProvider')]
    public function testOverlaps(DateRange $a, DateRange $b, ?DateRange $intersection = null, $gap = null): void
    {
        $expected = $intersection !== null;

        self::assertEquals($expected, $a->overlaps($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testIntersection(DateRange $a, DateRange $b, ?DateRange $intersection = null, $gap = null): void
    {
        if ($intersection === null) {
            $this->expectException(NoOverlap::class);
        }
        self::assertEquals($intersection, $a->intersection($b));
    }

    #[Depends('testOverlaps')]
    #[DataProvider('rangesProvider')]
    public function testGap(DateRange $a, DateRange $b, ?DateRange $gap = null, $intersection = null): void
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
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2026, 1, 1),
                ),
                'abuts' => true,
            ],
            'Abuts.1' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 28),
                ),
                'abuts' => true,
            ],
            'DoesNotAbut.0' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 27),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.1' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 29),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.2' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 31),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.3' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'abuts' => false,
            ],
            'DoesNotAbut.4' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 31),
                    to: Date::of(2026, 1, 1),
                ),
                'abuts' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('abutsProvider')]
    public function testAbuts(DateRange $a, DateRange $b, bool $abuts): void
    {
        self::assertEquals($abuts, $a->abuts($b));
        self::assertEquals($abuts, $b->abuts($a));
    }

    public static function equalityProvider(): array
    {
        return [
            'Equal.0' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'equals' => true,
            ],
            'Equal.1' => [
                DateRange::of(
                    from: Date::of(2025, 12, 31),
                    to: Date::of(2026, 1, 1),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 31),
                    to: Date::of(2026, 1, 1),
                ),
                'equals' => true,
            ],
            'NotEqual.0' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 29),
                ),
                'equals' => false,
            ],
            'NotEqual.1' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 27),
                ),
                'equals' => false,
            ],
            'NotEqual.2' => [
                DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2025, 12, 31),
                ),
                'equals' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('equalityProvider')]
    public function testEquality(DateRange $a, DateRange $b, bool $equals): void
    {
        self::assertEquals($equals, $a->is($b));
        self::assertNotEquals($equals, $a->isNot($b));
    }

    public static function isBeforeProvider(): array
    {
        return [
            'Range.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 31),
                    to: Date::of(2026, 1, 1),
                ),
                'expected' => true,
            ],
            'Range.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 30),
                    to: Date::of(2026, 1, 1),
                ),
                'expected' => false,
            ],
            'Range.2' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 27),
                    to: Date::of(2025, 12, 29),
                ),
                'expected' => false,
            ],
            'Range.3' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 27),
                    to: Date::of(2025, 12, 28),
                ),
                'expected' => false,
            ],
            'Range.4' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 26),
                    to: Date::of(2025, 12, 27),
                ),
                'expected' => false,
            ],
            'Date.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 31),
                'expected' => true,
            ],
            'Date.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 30),
                'expected' => false,
            ],
            'Date.2' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 29),
                'expected' => false,
            ],
            'Date.3' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 28),
                'expected' => false,
            ],
            'Date.4' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 27),
                'expected' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(DateRange $range, DateRange|Date $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isBefore($item));
    }

    public static function isAfterProvider(): array
    {
        return [
            'Range.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 27),
                ),
                'expected' => true,
            ],
            'Range.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 25),
                    to: Date::of(2025, 12, 28),
                ),
                'expected' => false,
            ],
            'Range.2' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 29),
                ),
                'expected' => false,
            ],
            'Range.3' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 29),
                    to: Date::of(2025, 12, 31),
                ),
                'expected' => false,
            ],
            'Range.4' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => DateRange::of(
                    from: Date::of(2025, 12, 31),
                    to: Date::of(2025, 12, 31),
                ),
                'expected' => false,
            ],
            'Date.0' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 27),
                'expected' => true,
            ],
            'Date.1' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 28),
                'expected' => false,
            ],
            'Date.2' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 29),
                'expected' => false,
            ],
            'Date.3' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 30),
                'expected' => false,
            ],
            'Date.4' => [
                'range' => DateRange::of(
                    from: Date::of(2025, 12, 28),
                    to: Date::of(2025, 12, 30),
                ),
                'item' => Date::of(2025, 12, 31),
                'expected' => false,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(DateRange $range, DateRange|Date $item, bool $expected): void
    {
        self::assertEquals($expected, $range->isAfter($item));
    }
}
