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

use Kronika\Duration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(Duration::class)]
final class DurationTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    public static function basicProvider(): array
    {
        return [
            'Micros:Zero' => [['micros' => 0], ['seconds' => 0, 'micros' => 0]],
            'Micros:1' => [['micros' => 1], ['seconds' => 0, 'micros' => 1]],
            'Micros:123' => [['micros' => 123], ['micros' => 123]],
            'Micros:500' => [['micros' => 500], ['micros' => 500]],
            'Micros:500123' => [['micros' => 500123], ['micros' => 500123]],
            'Micros:1500123' => [['micros' => 1500123], ['seconds' => 1, 'micros' => 500123]],
            'Seconds:Zero' => [['seconds' => 0], ['seconds' => 0, 'micros' => 0]],
            'Seconds:30' => [['seconds' => 30], ['seconds' => 30]],
            'Seconds:60' => [['seconds' => 60], ['minutes' => 1]],
            'Seconds:90' => [['seconds' => 90], ['minutes' => 1, 'seconds' => 30]],
            'Seconds:7979' => [['seconds' => 7979], ['hours' => 2, 'minutes' => 12, 'seconds' => 59]],
            'Seconds:345599' => [['seconds' => 345599], ['days' => 3, 'hours' => 23, 'minutes' => 59, 'seconds' => 59]],
            'Seconds:2764799' => [['seconds' => 2764799], ['days' => 31, 'hours' => 23, 'minutes' => 59, 'seconds' => 59]],
            'Seconds:31622399' => [['seconds' => 31622399], ['days' => 365, 'hours' => 23, 'minutes' => 59, 'seconds' => 59]],
            'Seconds:63158399' => [['seconds' => 63158399], ['days' => 730, 'hours' => 23, 'minutes' => 59, 'seconds' => 59]],
            'Minutes:25' => [['minutes' => 25], ['minutes' => 25]],
            'Minutes:59' => [['minutes' => 59], ['minutes' => 59]],
            'Minutes:60' => [['minutes' => 60], ['hours' => 1]],
            'Minutes:75' => [['minutes' => 75], ['hours' => 1, 'minutes' => 15]],
            'Minutes:1439' => [['minutes' => 1439], ['hours' => 23, 'minutes' => 59]],
            'Minutes:1440' => [['minutes' => 1440], ['days' => 1]],
            'Minutes:2190' => [['minutes' => 2190], ['days' => 1, 'hours' => 12, 'minutes' => 30]],
            'Minutes:47490' => [['minutes' => 47490], ['days' => 32, 'hours' => 23, 'minutes' => 30]],
            'Minutes:527760' => [['minutes' => 527760], ['days' => 366, 'hours' => 12]],
            'Hours:1' => [['hours' => 1], ['hours' => 1]],
            'Hours:12' => [['hours' => 12], ['hours' => 12]],
            'Hours:23' => [['hours' => 23], ['hours' => 23]],
            'Hours:24' => [['hours' => 24], ['days' => 1]],
            'Hours:25' => [['hours' => 25], ['days' => 1, 'hours' => 1]],
            'Hours:336' => [['hours' => 336], ['days' => 14]],
            'Hours:337' => [['hours' => 337], ['days' => 14, 'hours' => 1]],
            'Hours:359' => [['hours' => 359], ['days' => 14, 'hours' => 23]],
            'Hours:2091' => [['hours' => 2091], ['days' => 87, 'hours' => 3]],
            'Hours:11483' => [['hours' => 11483], ['days' => 478, 'hours' => 11]],
            'Hours:36011' => [['hours' => 36011], ['days' => 1500, 'hours' => 11]],
            'Hours:12.Minutes:30' => [['hours' => 12, 'minutes' => 30], ['hours' => 12, 'minutes' => 30]],
            'Hours:23.Seconds:105' => [['hours' => 23, 'seconds' => 105], ['hours' => 23, 'minutes' => 1, 'seconds' => 45]],
            'Hours:23.Minutes:59.Seconds:59' => [
                ['hours' => 23, 'minutes' => 59, 'seconds' => 59],
                ['hours' => 23, 'minutes' => 59, 'seconds' => 59],
            ],
            'Hours:23.Minutes:59.Seconds:60' => [['hours' => 23, 'minutes' => 59, 'seconds' => 60], ['days' => 1]],
            'Hours:23.Minutes:59.Seconds:59.Micros:1_000_000' => [
                ['hours' => 23, 'minutes' => 59, 'seconds' => 59, 'micros' => 1_000_000],
                ['days' => 1],
            ],
            'Hours:23.Minutes:59.Seconds:59.Micros:1_000_001' => [
                ['hours' => 23, 'minutes' => 59, 'seconds' => 59, 'micros' => 1_000_001],
                ['days' => 1, 'micros' => 1],
            ],
            'Days:1' => [['days' => 1], ['days' => 1]],
            'Days:5' => [['days' => 5], ['days' => 5]],
            'Days:7' => [['days' => 7], ['days' => 7]],
            'Days:14' => [['days' => 14], ['days' => 14]],
            'Days:30' => [['days' => 30], ['days' => 30]],
            'Days:31' => [['days' => 31], ['days' => 31]],
            'Days:60' => [['days' => 60], ['days' => 60]],
            'Days:201' => [['days' => 201], ['days' => 201]],
            'Days:365' => [['days' => 365], ['days' => 365]],
            'Days:724' => [['days' => 724], ['days' => 724]],
            'Days:2575' => [['days' => 2575], ['days' => 2575]],
            'Days:3.Hours:0.Minutes:59.Seconds:59' => [
                ['days' => 3, 'minutes' => 59, 'seconds' => 59],
                ['days' => 3, 'minutes' => 59, 'seconds' => 59],
            ],
            'Days:3.Hours:23.Minutes:59.Seconds:59' => [
                ['days' => 3, 'hours' => 23, 'minutes' => 59, 'seconds' => 59],
                ['days' => 3, 'hours' => 23, 'minutes' => 59, 'seconds' => 59],
            ],
            'Days:31.Hours:12.Minutes:30.Seconds:30' => [
                ['days' => 31, 'hours' => 12, 'minutes' => 30, 'seconds' => 30],
                ['days' => 31, 'hours' => 12, 'minutes' => 30, 'seconds' => 30],
            ],
            'Days:364.Hours:12.Minutes:30' => [
                ['days' => 364, 'hours' => 12, 'minutes' => 30],
                ['days' => 364, 'hours' => 12, 'minutes' => 30],
            ],
            'Days:364.Hours:23.Minutes:59.Seconds:60' => [
                ['days' => 364, 'hours' => 23, 'minutes' => 59, 'seconds' => 60],
                ['days' => 365],
            ],
            'Days:364.Hours:23.Minutes:59.Seconds:61' => [
                ['days' => 364, 'hours' => 23, 'minutes' => 59, 'seconds' => 61],
                ['days' => 365, 'seconds' => 1],
            ],
        ];
    }

    /**
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $args
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $expected
     */
    #[DataProvider('basicProvider')]
    public function testBasic(array $args, array $expected): void
    {
        $duration = Duration::of(...$args);

        self::assertEquals($expected['days'] ?? 0, $duration->days());
        self::assertEquals($expected['hours'] ?? 0, $duration->hours());
        self::assertEquals($expected['minutes'] ?? 0, $duration->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $duration->seconds());
        self::assertEquals($expected['micros'] ?? 0, $duration->microseconds());
        self::assertEquals(\array_sum($args) === 0, $duration->isZero());
        self::assertSame(Duration::of(...$args), $duration);
        self::assertNotSame(Duration::of(
            ...[...$args, 'seconds' => ($args['seconds'] ?? 0) + 1]),
            $duration,
        );
    }

    /**
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $args
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $expected
     */
    #[Depends('testBasic')]
    #[DataProvider('basicProvider')]
    public function testInMethods(array $args, array $expected): void
    {
        $duration = Duration::of(...$args);

        $inDays = $expected['days'] ?? 0;
        $inHours = ($expected['hours'] ?? 0) + $inDays * 24;
        $inMinutes = ($expected['minutes'] ?? 0) + $inHours * 60;
        $inSeconds = ($expected['seconds'] ?? 0) + $inMinutes * 60;

        self::assertEquals($inDays, $duration->inDays());
        self::assertEquals($inHours, $duration->inHours());
        self::assertEquals($inMinutes, $duration->inMinutes());
        self::assertEquals($inSeconds, $duration->inSeconds());
    }

    /**
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $args
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $expected
     */
    #[Depends('testInMethods')]
    #[DataProvider('basicProvider')]
    public function testRoundMethods(array $args, array $expected): void
    {
        $duration = Duration::of(...$args);

        $actual = $duration->roundToSeconds();
        self::assertEquals($expected['days'] ?? 0, $actual->days());
        self::assertEquals($expected['hours'] ?? 0, $actual->hours());
        self::assertEquals($expected['minutes'] ?? 0, $actual->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $actual->seconds());
        self::assertEquals(0, $actual->microseconds());

        $actual = $duration->roundToMinutes();
        self::assertEquals($expected['days'] ?? 0, $actual->days());
        self::assertEquals($expected['hours'] ?? 0, $actual->hours());
        self::assertEquals($expected['minutes'] ?? 0, $actual->minutes());
        self::assertEquals(0, $actual->seconds());
        self::assertEquals(0, $actual->microseconds());

        $actual = $duration->roundToHours();
        self::assertEquals($expected['days'] ?? 0, $actual->days());
        self::assertEquals($expected['hours'] ?? 0, $actual->hours());
        self::assertEquals(0, $actual->minutes());
        self::assertEquals(0, $actual->seconds());
        self::assertEquals(0, $actual->microseconds());

        $actual = $duration->roundToDays();
        self::assertEquals($expected['days'] ?? 0, $actual->days());
        self::assertEquals(0, $actual->hours());
        self::assertEquals(0, $actual->minutes());
        self::assertEquals(0, $actual->seconds());
        self::assertEquals(0, $actual->microseconds());
    }

    /**
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $args
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $expected
     */
    #[Depends('testBasic')]
    #[Depends('testRoundMethods')]
    #[DataProvider('basicProvider')]
    public function testDropMethods(array $args, array $expected): void
    {
        $duration = Duration::of(...$args);

        $actual = $duration->dropToHours();
        self::assertEquals(0, $actual->days());
        self::assertEquals($expected['hours'] ?? 0, $actual->hours());
        self::assertEquals($expected['minutes'] ?? 0, $actual->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $actual->seconds());
        self::assertEquals($expected['micros'] ?? 0, $actual->microseconds());

        $actual = $duration->dropToMinutes();
        self::assertEquals(0, $actual->days());
        self::assertEquals(0, $actual->hours());
        self::assertEquals($expected['minutes'] ?? 0, $actual->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $actual->seconds());
        self::assertEquals($expected['micros'] ?? 0, $actual->microseconds());

        $actual = $duration->dropToSeconds();
        self::assertEquals(0, $actual->days());
        self::assertEquals(0, $actual->hours());
        self::assertEquals(0, $actual->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $actual->seconds());
        self::assertEquals($expected['micros'] ?? 0, $actual->microseconds());
    }

    public static function addProvider(): array
    {
        return [
            [
                Duration::zero(), Duration::zero(), Duration::zero(),
                ['seconds' => 0],
            ],
            [
                Duration::zero(), Duration::zero(), Duration::of(micros: 999_999),
                ['micros' => 999_999],
            ],
            [
                Duration::zero(), Duration::of(micros: 2), Duration::of(micros: 999_999),
                ['seconds' => 1, 'micros' => 1],
            ],
            [
                Duration::zero(), Duration::zero(), Duration::of(seconds: 14),
                ['seconds' => 14],
            ],
            [
                Duration::zero(), Duration::of(seconds: 45), Duration::of(micros: 550_000),
                ['seconds' => 45, 'micros' => 550_000],
            ],
            [
                Duration::zero(), Duration::of(seconds: 45), Duration::of(seconds: 14),
                ['seconds' => 59],
            ],
            [
                Duration::zero(), Duration::of(seconds: 45), Duration::of(seconds: 14, micros: 555),
                ['seconds' => 59, 'micros' => 555],
            ],
            [
                Duration::of(seconds: 45), Duration::of(seconds: 10), Duration::of(seconds: 5),
                ['minutes' => 1],
            ],
            [
                Duration::of(seconds: 45), Duration::of(seconds: 10, micros: 999_999), Duration::of(seconds: 4, micros: 1),
                ['minutes' => 1],
            ],
            [
                Duration::of(seconds: 45), Duration::of(seconds: 10), Duration::of(seconds: 10),
                ['minutes' => 1, 'seconds' => 5],
            ],
            [
                Duration::of(minutes: 59, seconds: 45), Duration::of(seconds: 10), Duration::of(seconds: 4),
                ['minutes' => 59, 'seconds' => 59],
            ],
            [
                Duration::of(minutes: 59, seconds: 45), Duration::of(seconds: 10), Duration::of(seconds: 5),
                ['hours' => 1],
            ],
            [
                Duration::of(minutes: 59, seconds: 45), Duration::of(hours: 1, seconds: 10), Duration::of(seconds: 4),
                ['hours' => 1, 'minutes' => 59, 'seconds' => 59],
            ],
            [
                Duration::of(minutes: 59, seconds: 45), Duration::of(hours: 1, seconds: 10), Duration::of(seconds: 5),
                ['hours' => 2],
            ],
            [
                Duration::of(minutes: 59, seconds: 45), Duration::of(hours: 1, seconds: 10), Duration::of(seconds: 6),
                ['hours' => 2, 'seconds' => 1],
            ],
            [
                Duration::of(minutes: 59, seconds: 45), Duration::of(hours: 1, seconds: 30), Duration::of(seconds: 77),
                ['hours' => 2, 'minutes' => 1, 'seconds' => 32],
            ],
            [
                Duration::of(minutes: 59, seconds: 45),
                Duration::of(hours: 23, seconds: 30),
                Duration::of(days: 29, seconds: 77),
                ['days' => 30, 'hours' => 0, 'minutes' => 1, 'seconds' => 32],
            ],
        ];
    }

    /**
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $expected
     */
    #[Depends('testBasic')]
    #[DataProvider('addProvider')]
    public function testAdd(Duration $a, Duration $b, Duration $c, array $expected): void
    {
        $actual = $a->add($b, $c);

        self::assertEquals($expected['days'] ?? 0, $actual->days());
        self::assertEquals($expected['hours'] ?? 0, $actual->hours());
        self::assertEquals($expected['minutes'] ?? 0, $actual->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $actual->seconds());
        self::assertEquals($expected['micros'] ?? 0, $actual->microseconds());
    }

    public static function subProvider(): array
    {
        return [
            [
                Duration::zero(), Duration::zero(), Duration::zero(),
                ['seconds' => 0],
            ],
            [
                Duration::zero(), Duration::zero(), Duration::of(micros: 1),
                ['seconds' => 0],
            ],
            [
                Duration::zero(), Duration::zero(), Duration::of(seconds: 14),
                ['seconds' => 0],
            ],
            [
                Duration::of(seconds: 14), Duration::of(seconds: 45), Duration::zero(),
                ['seconds' => 0],
            ],
            [
                Duration::of(seconds: 14), Duration::of(micros: 1), Duration::zero(),
                ['seconds' => 13, 'micros' => 999_999],
            ],
            [
                Duration::of(seconds: 14), Duration::of(seconds: 13), Duration::zero(),
                ['seconds' => 1],
            ],
            [
                Duration::of(seconds: 14), Duration::of(seconds: 13), Duration::of(micros: 1),
                ['seconds' => 0, 'micros' => 999_999],
            ],
            [
                Duration::of(seconds: 45), Duration::of(seconds: 10), Duration::of(seconds: 5),
                ['seconds' => 30],
            ],
            [
                Duration::of(seconds: 45), Duration::of(seconds: 10), Duration::of(seconds: 5, micros: 1),
                ['seconds' => 29, 'micros' => 999_999],
            ],
            [
                Duration::of(minutes: 1, seconds: 10), Duration::of(seconds: 10), Duration::of(seconds: 5),
                ['seconds' => 55],
            ],
            [
                Duration::of(hours: 1, minutes: 1, seconds: 45),
                Duration::of(seconds: 30),
                Duration::of(minutes: 1, seconds: 20),
                ['minutes' => 59, 'seconds' => 55],
            ],
            [
                Duration::of(days: 29, seconds: 75),
                Duration::of(minutes: 59, seconds: 45),
                Duration::of(hours: 23, seconds: 30),
                ['days' => 28, 'hours' => 0, 'minutes' => 1],
            ],
            [
                Duration::of(days: 29, seconds: 75, micros: 500_000),
                Duration::of(minutes: 59, seconds: 45, micros: 200_000),
                Duration::of(hours: 23, seconds: 30, micros: 250_050),
                ['days' => 28, 'hours' => 0, 'minutes' => 1, 'micros' => 49_950],
            ],
        ];
    }

    /**
     * @param array{days?: int, hours?: int, minutes?: int, seconds?: int, micros?: int} $expected
     */
    #[Depends('testBasic')]
    #[DataProvider('subProvider')]
    public function testSub(Duration $a, Duration $b, Duration $c, array $expected): void
    {
        $actual = $a->sub($b, $c);

        self::assertEquals($expected['days'] ?? 0, $actual->days());
        self::assertEquals($expected['hours'] ?? 0, $actual->hours());
        self::assertEquals($expected['minutes'] ?? 0, $actual->minutes());
        self::assertEquals($expected['seconds'] ?? 0, $actual->seconds());
        self::assertEquals($expected['micros'] ?? 0, $actual->microseconds());
    }

    public static function comparisonProvider(): array
    {
        return [
            [Duration::zero(), Duration::zero(), self::EQUAL],
            [Duration::zero(), Duration::of(seconds: 1), self::LESS],
            [Duration::of(seconds: 1), Duration::zero(), self::GREATER],
            [Duration::zero(), Duration::of(micros: 1), self::LESS],
            [Duration::of(micros: 1), Duration::zero(), self::GREATER],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Duration $a, Duration $b, int $expected): void
    {
        $compared = $a->compareTo($b);

        self::assertEquals($expected, $compared->value());
        self::assertEquals($compared->less(), $a->isLessThan($b));
        self::assertEquals($compared->lessOrEqual(), $a->isLessThanOrEqualTo($b));
        self::assertEquals($compared->equal(), $a->is($b));
        self::assertEquals($compared->notEqual(), $a->isNot($b));
        self::assertEquals($compared->greaterOrEqual(), $a->isGreaterThanOrEqualTo($b));
        self::assertEquals($compared->greater(), $a->isGreaterThan($b));
    }

    #[Depends('testBasic')]
    public function testOfWeek(): void
    {
        self::assertEquals(Duration::of(days: 7), Duration::ofWeek());
        self::assertSame(Duration::of(days: 7), Duration::ofWeek());
    }

    #[Depends('testBasic')]
    public function testOfDay(): void
    {
        self::assertEquals(Duration::of(days: 1), Duration::ofDay());
        self::assertSame(Duration::of(days: 1), Duration::ofDay());
    }

    #[Depends('testBasic')]
    public function testOfHour(): void
    {
        self::assertEquals(Duration::of(hours: 1), Duration::ofHour());
        self::assertSame(Duration::of(hours: 1), Duration::ofHour());
    }

    #[Depends('testBasic')]
    public function testOfMinute(): void
    {
        self::assertEquals(Duration::of(minutes: 1), Duration::ofMinute());
        self::assertSame(Duration::of(minutes: 1), Duration::ofMinute());
    }

    #[Depends('testBasic')]
    public function testOfSecond(): void
    {
        self::assertEquals(Duration::of(seconds: 1), Duration::ofSecond());
        self::assertSame(Duration::of(seconds: 1), Duration::ofSecond());
    }
}
