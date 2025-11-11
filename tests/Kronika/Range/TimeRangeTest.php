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
use Kronika\Precision;
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
        $since = Time::of(12, 35, Time\Second::of(45, 123));
        $till = Time::of(21, 50, Time\Second::of(45, 999));
        $duration = $since->until($till);
        $range = TimeRange::of($since, $till);

        self::assertEquals($since, $range->since());
        self::assertEquals($till, $range->till());
        self::assertTrue($range->contains($since));
        self::assertFalse($range->contains($till));
        self::assertEquals($duration, $range->duration());
        self::assertEquals($duration->isZero(), $range->isZero());
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $since = Time::midday();
        $till = Time::endOfDay();
        $range = TimeRange::of($since, $till);

        self::assertEquals(\sprintf('%s – %s', $since, $till), (string)$range);
    }

    public static function eachProvider(): array
    {
        return [
            'Hours.0' => [
                TimeRange::of(
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(50)),
                ),
                Duration::ofHour(),
                [
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(13, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(45, 123)),
                ],
            ],
            'Hours.1' => [
                TimeRange::of(
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(50)),
                    precision: Precision::Minute,
                ),
                Duration::ofHour(),
                [
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(13, 35, Time\Second::of(45, 123)),
                ],
            ],
            'Hours.2' => [
                TimeRange::of(
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(50)),
                ),
                Duration::of(hours: 2),
                [
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(45, 123)),
                ],
            ],
            'Hours.3' => [
                TimeRange::of(
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(45)),
                    precision: Precision::Micro,
                ),
                Duration::of(hours: 2),
                [
                    Time::of(12, 35, Time\Second::of(45, 123)),
                ],
            ],
            'Minutes.0' => [
                TimeRange::of(
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(50)),
                ),
                Duration::of(minutes: 30),
                [
                    Time::of(12, 35, Time\Second::of(45, 123)),
                    Time::of(13, 05, Time\Second::of(45, 123)),
                    Time::of(13, 35, Time\Second::of(45, 123)),
                    Time::of(14, 05, Time\Second::of(45, 123)),
                    Time::of(14, 35, Time\Second::of(45, 123)),
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
                    Time::of(12, 35, Second::of(50, 999)),
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
                    till: null,
                ),
                Duration::of(seconds: 1),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                ],
            ],
            'Zero.0' => [
                TimeRange::of(
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(47, 999)),
                ),
                Duration::zero(),
                [
                    Time::of(12, 35, Second::of(45, 123)),
                    Time::of(12, 35, Second::of(46, 123)),
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
}
