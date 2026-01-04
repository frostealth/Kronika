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
use Kronika\Range\Exception\InvalidRange;
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

    #[DependsExternal(ZonedDateTimeRangeTest::class, 'testBasic')]
    public function testAt(): void
    {
        $timezone = new \DateTimeZone('Europe/Amsterdam');
        $from = LocalDateTime::of(Date::of(2025, 12, 15), Time::midday());
        $to = LocalDateTime::of(Date::of(2025, 12, 16), Time::endOfDay());
        $range = LocalDateTimeRange::of($from, $to);

        $actual = $range->at($timezone);

        self::assertEquals(ZonedDateTimeRange::of($from->at($timezone), $to->at($timezone)), $actual);
    }
}
