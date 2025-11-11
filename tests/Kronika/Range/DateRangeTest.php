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
        $since = Date::of(2025, 12, 15);
        $till = Date::of(2026, 1, 1);
        $duration = $since->until($till);

        $range = DateRange::of($since, $till);

        self::assertEquals($since, $range->since());
        self::assertEquals($till, $range->till());
        self::assertTrue($range->contains($since));
        self::assertFalse($range->contains($till));
        self::assertFalse($range->isZero());
        self::assertEquals($duration, $range->duration());
    }

    #[Depends('testBasic')]
    public function testAround(): void
    {
        $mid = Date::of(2025, 12, 15);
        $duration = Duration::ofWeek();
        $since = $mid->sub($duration);
        $till = $mid->add($duration);

        $range = DateRange::around($mid, $duration);

        self::assertEquals($since, $range->since());
        self::assertEquals($till, $range->till());
        self::assertTrue($range->contains($since));
        self::assertFalse($range->contains($till));
        self::assertEquals($since->until($till), $range->duration());
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $since = Date::of(2025, 12, 15);
        $till = Date::of(2026, 1, 1);
        $range = DateRange::of($since, $till);

        self::assertEquals(\sprintf('%s – %s', $since, $till), (string)$range);
    }

    public static function eachProvider(): array
    {
        return [
            [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2026, 1, 1)),
                Duration::ofDay(),
                [
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 29),
                    Date::of(2025, 12, 30),
                    Date::of(2025, 12, 31),
                ],
            ],
            [
                DateRange::of(Date::of(2025, 12, 26), Date::of(2026, 1, 2)),
                Duration::of(days: 2),
                [
                    Date::of(2025, 12, 26),
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 30),
                    Date::of(2026, 1, 1),
                ],
            ],
            [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 31)),
                Duration::of(hours: 2),
                [
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 29),
                    Date::of(2025, 12, 30),
                ],
            ],
            [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 31)),
                Duration::zero(),
                [
                    Date::of(2025, 12, 28),
                    Date::of(2025, 12, 29),
                    Date::of(2025, 12, 30),
                ],
            ],
            [
                DateRange::of(Date::of(2025, 12, 28), Date::of(2025, 12, 28)),
                Duration::ofDay(),
                [
                    Date::of(2025, 12, 28),
                ],
            ],
            [
                DateRange::of(Date::of(2025, 12, 28), till: null),
                Duration::ofDay(),
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
}
