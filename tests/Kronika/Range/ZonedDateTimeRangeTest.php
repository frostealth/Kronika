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
}
