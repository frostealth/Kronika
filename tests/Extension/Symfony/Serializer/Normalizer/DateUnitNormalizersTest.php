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

namespace Kronika\Extension\Tests\Symfony\Serializer\Normalizer;

use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Extension\Symfony\Serializer\Normalizer\DayOfMonthNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\DayOfWeekNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\MonthNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\YearNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(YearNormalizer::class)]
#[CoversClass(MonthNormalizer::class)]
#[CoversClass(DayOfMonthNormalizer::class)]
#[CoversClass(DayOfWeekNormalizer::class)]
final class DateUnitNormalizersTest extends TestCase
{
    private static Serializer $serializer;
    private static Year $year;
    private static Month $month;
    private static DayOfMonth $dayOfMonth;
    private static DayOfWeek $dayOfWeek;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$year = Year::of(2025);
        self::$month = Month::April;
        self::$dayOfMonth = DayOfMonth::of(04);
        self::$dayOfWeek = DayOfWeek::Wednesday;

        self::$serializer = new Serializer(
            normalizers: [
                new YearNormalizer(),
                new MonthNormalizer(),
                new DayOfMonthNormalizer(),
                new DayOfWeekNormalizer(),
            ],
        );
    }

    public function testYearNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$year);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$year->number(), $actual);
    }

    public function testYearDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$year->number(), Year::class);

        self::assertInstanceOf(Year::class, $actual);
        self::assertEquals(self::$year, $actual);
    }

    public function testMonthNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$month);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$month->number(), $actual);
    }

    public function testMonthDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$month->number(), Month::class);

        self::assertInstanceOf(Month::class, $actual);
        self::assertEquals(self::$month, $actual);
    }

    public function testDayOfMonthNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$dayOfMonth);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$dayOfMonth->number(), $actual);
    }

    public function testDayOfMonthDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$dayOfMonth->number(), DayOfMonth::class);

        self::assertInstanceOf(DayOfMonth::class, $actual);
        self::assertEquals(self::$dayOfMonth, $actual);
    }

    public function testDayOfWeekNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$dayOfWeek);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$dayOfWeek->number(), $actual);
    }

    public function testDayOfWeekDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$dayOfWeek->number(), DayOfWeek::class);

        self::assertInstanceOf(DayOfWeek::class, $actual);
        self::assertEquals(self::$dayOfWeek, $actual);
    }
}
