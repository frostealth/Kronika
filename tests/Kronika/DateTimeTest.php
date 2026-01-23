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

use Kronika\Date;
use Kronika\DateTime;
use Kronika\Duration;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Precision;
use Kronika\Time;
use Kronika\Time\Second;
use Kronika\Unit;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClassesThatImplementInterface(DateTime::class)]
final class DateTimeTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    public static function comparisonProvider(): array
    {
        return [
            // LocalDateTime, Precision::Micro
            'LocalDateTime.Micro.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Micro.Microsecond.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Microsecond.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Hour.Greater' => [
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.Month.Less' => [
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Month.Greater' => [
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.Year.Less' => [
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Year.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.Epoch.Equal' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 4545),
                self::localOf(1970, 1, 1, 0, 0, 0, 4545),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Micro.Epoch.Less' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 1),
                self::localOf(1970, 1, 1, 0, 0, 0, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.Epoch.Greater' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.ZonedDateTime.Equal' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, '+01:00'),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Micro.ZonedDateTime.Less' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 5555, '+01:00'),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Micro.ZonedDateTime.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 5555),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, '+01:00'),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Micro.ZonedDateTime.DST.Equal' => [
                self::localOf(2006, 4, 2, 2, 15, 45, 5555),
                self::zonedOf(2006, 4, 2, 3, 15, 45, 5555, 'America/New_York'),
                Precision::Micro,
                self::EQUAL,
            ],
            // LocalDateTime, Precision::Second
            'LocalDateTime.Second.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::EQUAL,
            ],
            'LocalDateTime.Second.Microsecond.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Second,
                self::EQUAL,
            ],
            'LocalDateTime.Second.Microsecond.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::EQUAL,
            ],
            'LocalDateTime.Second.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.Hour.Greater' => [
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.Month.Less' => [
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.Month.Greater' => [
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.Year.Less' => [
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.Year.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.Epoch.Equal' => [
                self::localOf(1969, 12, 31, 23, 59, 59, 4545),
                self::localOf(1969, 12, 31, 23, 59, 59, 1),
                Precision::Second,
                self::EQUAL,
            ],
            'LocalDateTime.Second.Epoch.Less' => [
                self::localOf(1969, 12, 31, 23, 59, 58, 4545),
                self::localOf(1969, 12, 31, 23, 59, 59, 4545),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.Epoch.Greater' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
                Precision::Second,
                self::GREATER,
            ],
            'LocalDateTime.Second.ZonedDateTime.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, '+01:00'),
                Precision::Second,
                self::EQUAL,
            ],
            'LocalDateTime.Second.ZonedDateTime.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                Precision::Second,
                self::LESS,
            ],
            'LocalDateTime.Second.ZonedDateTime.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                Precision::Second,
                self::GREATER,
            ],
            // LocalDateTime, Precision::Minute
            'Minute.Second.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'LocalDateTime.Minute.Microsecond.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Minute,
                self::EQUAL,
            ],
            'LocalDateTime.Minute.Microsecond.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'LocalDateTime.Minute.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'LocalDateTime.Minute.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Minute,
                self::EQUAL,
            ],
            'LocalDateTime.Minute.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'LocalDateTime.Minute.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'LocalDateTime.Minute.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'LocalDateTime.Minute.Hour.Greater' => [
                self::localOf(2025, 10, 30, 20, 15, 45, 4545),
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'LocalDateTime.Minute.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'LocalDateTime.Minute.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 31, 12, 15, 45, 4545),
                self::localOf(2025, 10, 30, 20, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'LocalDateTime.Minute.Month.Less' => [
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'LocalDateTime.Minute.Month.Greater' => [
                self::localOf(2025, 12, 30, 12, 15, 45, 4545),
                self::localOf(2025, 10, 31, 20, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'LocalDateTime.Minute.Year.Less' => [
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'LocalDateTime.Minute.Year.Greater' => [
                self::localOf(2026, 10, 30, 12, 15, 45, 4545),
                self::localOf(2025, 12, 31, 20, 20, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'LocalDateTime.Minute.ZonedDateTime.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, '+01:00'),
                Precision::Minute,
                self::EQUAL,
            ],
            'LocalDateTime.Minute.ZonedDateTime.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, '+01:00'),
                Precision::Minute,
                self::LESS,
            ],
            'LocalDateTime.Minute.ZonedDateTime.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Precision::Minute,
                self::GREATER,
            ],
            // LocalDateTime, Date and DateUnit
            'LocalDateTime.Date.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date::of(2025, 10, 30),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Date.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date::of(2025, 10, 31),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Date.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date::of(2025, 10, 29),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.Year.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Year::of(2025),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.Year.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Year::of(2026),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.Year.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\Year::of(2024),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.Month.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Month::of(10),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.Month.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\Month::of(11),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.Month.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\Month::of(9),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.DayOfMonth.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfMonth::of(30),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.DayOfMonth.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfMonth::of(31),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.DayOfMonth.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\DayOfMonth::of(29),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.DayOfWeek.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfWeek::Thursday,
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.DayOfWeek.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfWeek::Friday,
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.DayOfWeek.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\DayOfWeek::Tuesday,
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.DayOYear.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfYear::of(303),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.DayOYear.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Date\DayOfYear::of(304),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.DayOYear.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Date\DayOfYear::of(302),
                Precision::Micro,
                self::GREATER,
            ],
            // LocalDateTime, Time and TimeUnit
            'LocalDateTime.Time.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time::of(12, 15, Second::of(55, 5555)),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Time.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time::of(12, 15, Second::of(55, 9999)),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Time.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Time::of(12, 15, Second::of(55)),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.Hour.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Hour::of(12),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.Hour.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Hour::of(13),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.Hour.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Time\Hour::of(11),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.Minute.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Minute::of(15),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.Minute.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Minute::of(20),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.Minute.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 45, 45),
                Time\Minute::of(10),
                Precision::Micro,
                self::GREATER,
            ],
            'LocalDateTime.Unit.Second.Equal' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Second::of(55, 5555),
                Precision::Micro,
                self::EQUAL,
            ],
            'LocalDateTime.Unit.Second.Less' => [
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Time\Second::of(55, 999999),
                Precision::Micro,
                self::LESS,
            ],
            'LocalDateTime.Unit.Second.Greater' => [
                self::localOf(2025, 10, 30, 12, 20, 55, 5555),
                Time\Second::of(55),
                Precision::Micro,
                self::GREATER,
            ],

            // ZonedDateTime, Precision::Micro
            'ZonedDateTime.Micro.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Micro.Microsecond.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Microsecond.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Month.Less' => [
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Month.Greater' => [
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Year.Less' => [
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Year.Greater' => [
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Epoch.Less' => [
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, 'UTC'),
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, 'UTC'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Epoch.Greater' => [
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, 'UTC'),
                self::zonedOf(1969, 12, 31, 23, 59, 59, 1, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.Timezone.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 4545, '+01:00'),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Micro.Timezone.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 5555, '+01:00'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Timezone.Greater' => [
                self::zonedOf(2025, 10, 30, 13, 15, 45, 5555, '+01:00'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.LocalDateTime.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Micro.LocalDateTime.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.LocalDateTime.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Micro.LocalDateTime.DST.Equal' => [
                self::zonedOf(2006, 4, 2, 3, 15, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 2, 15, 55, 5555),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Micro.Native.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, '+01:00'),
                new \DateTime('2025-10-30 13:15:45.004545 +02:00'),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Micro.Native.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                new \DateTime('2025-10-30 13:15:55.004545 +02:00'),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Micro.Native.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                new \DateTime('2025-10-30 13:15:45.005555 +02:00'),
                Precision::Micro,
                self::GREATER,
            ],
            // ZonedDateTime, Precision::Second
            'ZonedDateTime.Second.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::EQUAL,
            ],
            'ZonedDateTime.Second.Microsecond.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Second,
                self::EQUAL,
            ],
            'ZonedDateTime.Second.Microsecond.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::EQUAL,
            ],
            'ZonedDateTime.Second.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.Month.Less' => [
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Month.Greater' => [
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.Year.Less' => [
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Year.Greater' => [
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.Timezone.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 4545, '+01:00'),
                Precision::Second,
                self::EQUAL,
            ],
            'ZonedDateTime.Second.Timezone.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 55, 4545, '+01:00'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Timezone.Greater' => [
                self::zonedOf(2026, 10, 30, 13, 15, 55, 4545, '+01:00'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.LocalDateTime.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Second,
                self::EQUAL,
            ],
            'ZonedDateTime.Second.LocalDateTime.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 55, 4545),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.LocalDateTime.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 45, 5555),
                Precision::Second,
                self::GREATER,
            ],
            'ZonedDateTime.Second.Native.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:45.004545 +02:00'),
                Precision::Second,
                self::EQUAL,
            ],
            'ZonedDateTime.Second.Native.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:55.004545 +02:00'),
                Precision::Second,
                self::LESS,
            ],
            'ZonedDateTime.Second.Native.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:45.005555 +02:00'),
                Precision::Second,
                self::GREATER,
            ],
            // ZonedDateTime, Precision::Minute
            'ZonedDateTime.Minute.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Microsecond.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Microsecond.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 45, 5555, 'UTC'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 20, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 31, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 30, 20, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.Month.Less' => [
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.Month.Greater' => [
                self::zonedOf(2025, 12, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 10, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.Year.Less' => [
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.Year.Greater' => [
                self::zonedOf(2026, 10, 30, 12, 15, 45, 4545, 'UTC'),
                self::zonedOf(2025, 12, 31, 20, 20, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.Timezone.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 15, 45, 4545, '+01:00'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Timezone.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                self::zonedOf(2025, 10, 30, 13, 20, 45, 4545, '+01:00'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.Timezone.Greater' => [
                self::zonedOf(2026, 10, 30, 13, 20, 45, 4545, '+01:00'),
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, 'UTC'),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.LocalDateTime.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 45, 4545),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.LocalDateTime.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                self::localOf(2025, 10, 30, 12, 20, 45, 4545),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.LocalDateTime.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, '+01:00'),
                self::localOf(2025, 10, 30, 12, 15, 55, 5555),
                Precision::Minute,
                self::GREATER,
            ],
            'ZonedDateTime.Minute.Native.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:45.004545 +02:00'),
                Precision::Minute,
                self::EQUAL,
            ],
            'ZonedDateTime.Minute.Native.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:20:45.004545 +02:00'),
                Precision::Minute,
                self::LESS,
            ],
            'ZonedDateTime.Minute.Native.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 4545, '+01:00'),
                new \DateTimeImmutable('2025-10-30 13:15:55.005555 +02:00'),
                Precision::Minute,
                self::GREATER,
            ],
            // LocalDateTime, Date and DateUnit
            'ZonedDateTime.Date.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date::of(2025, 10, 30),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Date.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date::of(2025, 10, 31),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Date.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date::of(2025, 10, 29),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.Year.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Year::of(2025),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.Year.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Year::of(2026),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.Year.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\Year::of(2024),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.Month.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Month::of(10),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.Month.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\Month::of(11),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.Month.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\Month::of(9),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.DayOfMonth.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(30),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.DayOfMonth.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(31),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.DayOfMonth.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\DayOfMonth::of(29),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.DayOfWeek.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfWeek::Thursday,
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.DayOfWeek.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfWeek::Friday,
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.DayOfWeek.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\DayOfWeek::Tuesday,
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.DayOfYear.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfYear::of(303),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.DayOfYear.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Date\DayOfYear::of(304),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.DayOfYear.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Date\DayOfYear::of(302),
                Precision::Micro,
                self::GREATER,
            ],
            // ZonedDateTime, Time and TimeUnit
            'ZonedDateTime.Time.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time::of(12, 15, Second::of(55, 5555)),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Time.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time::of(12, 15, Second::of(55, 9999)),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Time.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, '+01:00'),
                Time::of(12, 15, Second::of(55)),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.Hour.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Hour::of(12),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.Hour.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Hour::of(13),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.Hour.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Time\Hour::of(11),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.Minute.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Minute::of(15),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.Minute.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Minute::of(20),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.Minute.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 45, 45, '+01:00'),
                Time\Minute::of(10),
                Precision::Micro,
                self::GREATER,
            ],
            'ZonedDateTime.Unit.Second.Equal' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Second::of(55, 5555),
                Precision::Micro,
                self::EQUAL,
            ],
            'ZonedDateTime.Unit.Second.Less' => [
                self::zonedOf(2025, 10, 30, 12, 15, 55, 5555, '+01:00'),
                Time\Second::of(55, 999999),
                Precision::Micro,
                self::LESS,
            ],
            'ZonedDateTime.Unit.Second.Greater' => [
                self::zonedOf(2025, 10, 30, 12, 20, 55, 5555, '+01:00'),
                Time\Second::of(55),
                Precision::Micro,
                self::GREATER,
            ],
        ];
    }

    #[DependsOnClass(DurationTest::class)]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(DateTime $a, DateTime|Unit|\DateTimeInterface $b, Precision $precision, int $expected): void
    {
        self::assertFalse($a->isBefore($a, $precision));
        self::assertTrue($a->isBeforeOrEqualTo($a, $precision));
        self::assertTrue($a->is($a, $precision));
        self::assertFalse($a->isNot($a, $precision));
        self::assertTrue($a->isAfterOrEqualTo($a, $precision));
        self::assertFalse($a->isAfter($a, $precision));

        $comparison = $a->compareTo($b, $precision);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b, $precision));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b, $precision));
        self::assertEquals($comparison->greater(), $a->isAfter($b, $precision));
        self::assertEquals($comparison->equal(), $a->is($b, $precision));
        self::assertEquals($comparison->notEqual(), $a->isNot($b, $precision));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b, $precision));
        self::assertEquals($comparison->notEqual(), $a->isNot($b, $precision));
    }

    public static function resetProvider(): array
    {
        return [
            [self::localOf(2025, 12, 15, 12, 45, 55, 5555)],
            [self::localOf(2025, 12, 15, 0, 30, 30, 0)],
            [self::localOf(2025, 12, 15, 0, 30, 0, 0)],
            [self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00')],
            [self::zonedOf(2025, 12, 15, 0, 30, 30, 0, '+01:00')],
            [self::zonedOf(2025, 12, 15, 0, 30, 0, 0, '+01:00')],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('resetProvider')]
    public function testResetMicro(DateTime $datetime): void
    {
        $result = $datetime->resetMicro();

        self::assertSame($datetime->date(), $result->date());
        self::assertSame($datetime->hour(), $result->hour());
        self::assertSame($datetime->minute(), $result->minute());
        self::assertEquals($datetime->second()->second(), $result->second()->second());
        self::assertEquals(0, $result->second()->microsecond());
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('resetProvider')]
    public function testResetSecond(DateTime $datetime): void
    {
        $result = $datetime->resetSecond();

        self::assertSame($datetime->date(), $result->date());
        self::assertSame($datetime->hour(), $result->hour());
        self::assertSame($datetime->minute(), $result->minute());
        self::assertEquals(0, $result->second()->second());
        self::assertEquals(0, $result->second()->microsecond());
    }

    public static function untilProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.LocalDateTime.Zero' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 50, 5555),
                Duration::zero(),
            ],
            'LocalDateTime.LocalDateTime.Micros.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5_500),
                self::localOf(2025, 12, 15, 12, 45, 55, 6_000),
                Duration::of(micros: 500),
            ],
            'LocalDateTime.LocalDateTime.Micros.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 6_000),
                self::localOf(2025, 12, 15, 12, 45, 55, 5_555),
                Duration::zero(),
            ],
            'LocalDateTime.LocalDateTime.Micros.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5_555),
                self::localOf(2025, 12, 15, 12, 45, 56, 4_555),
                Duration::of(micros: 999_000),
            ],
            'LocalDateTime.LocalDateTime.Micros.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 999_999),
                self::localOf(2025, 12, 15, 12, 45, 56, 0),
                Duration::of(micros: 1),
            ],
            'LocalDateTime.LocalDateTime.Seconds.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 0),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 4),
            ],
            'LocalDateTime.LocalDateTime.Seconds.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'LocalDateTime.LocalDateTime.Seconds.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'LocalDateTime.LocalDateTime.Minutes.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 55, 5555),
                Duration::of(minutes: 5),
            ],
            'LocalDateTime.LocalDateTime.Minutes.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 55, 0),
                Duration::of(minutes: 4, seconds: 59, micros: 994_445),
            ],
            'LocalDateTime.LocalDateTime.Minutes.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 50, 0),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'LocalDateTime.LocalDateTime.Hours' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 13, 45, 55, 5555),
                Duration::of(hours: 1),
            ],
            'LocalDateTime.LocalDateTime.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 16, 12, 45, 55, 5555),
                Duration::of(days: 1),
            ],
            'LocalDateTime.Date.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date::of(2025, 12, 20),
                Duration::of(days: 5),
            ],
            'LocalDateTime.Date.Months' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date::of(2026, 1, 15),
                Duration::of(days: 31),
            ],
            'LocalDateTime.Unit.Year' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\Year::of(2026),
                Duration::of(days: 365),
            ],
            'LocalDateTime.Unit.Month' => [
                self::localOf(2025, 11, 15, 12, 45, 55, 5555),
                Date\Month::December,
                Duration::of(days: 30),
            ],
            'LocalDateTime.Unit.DayOfMonth' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'LocalDateTime.Unit.DayOfWeek' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'LocalDateTime.Unit.DayOfYear' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfYear::of(350),
                Duration::of(days: 1),
            ],
            'LocalDateTime.Time.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'LocalDateTime.Time.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'LocalDateTime.Time.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'LocalDateTime.Unit.Hour' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'LocalDateTime.Unit.Minute' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'LocalDateTime.Unit.Second.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'LocalDateTime.Unit.Second.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 0),
                Duration::of(seconds: 1, micros: 994_445),
            ],
            'LocalDateTime.Unit.Second.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, micro: 994_445),
                Duration::of(seconds: 2),
                Precision::Second,
            ],
            'LocalDateTime.Unit.Second.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57),
                Duration::of(seconds: 2),
                Precision::Second,
            ],
            'LocalDateTime.Unit.Second.4' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, micro: 994_445),
                Duration::zero(),
                Precision::Minute,
            ],

            // ZonedDateTime
            'ZonedDateTime.ZonedDateTime.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 50, 5555, '+01:00'),
                Duration::zero(),
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.ZonedDateTime.Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.ZonedDateTime.Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+00:00'),
                Duration::of(hours: 1),
            ],
            'ZonedDateTime.ZonedDateTime.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 16, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Date.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date::of(2025, 12, 20),
                Duration::of(days: 5),
            ],
            'ZonedDateTime.Date.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date::of(2026, 1, 15),
                Duration::of(days: 31),
            ],
            'ZonedDateTime.Unit.Year' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Year::of(2026),
                Duration::of(days: 365),
            ],
            'ZonedDateTime.Unit.Month' => [
                self::zonedOf(2025, 11, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Month::December,
                Duration::of(days: 30),
            ],
            'ZonedDateTime.Unit.DayOfMonth' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Unit.DayOfWeek' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Unit.DayOfYear' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfYear::of(350),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Time.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.Time.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.Time.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.Time.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.Unit.Hour' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'ZonedDateTime.Unit.Hour.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(2),
                Duration::of(hours: 1),
            ],
            'ZonedDateTime.Unit.Hour.DST.1' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(3),
                Duration::of(hours: 1),
            ],
            'ZonedDateTime.Unit.Hour.DST.3' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(4),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.Unit.Hour.DST.4' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(4),
                Duration::of(hours: 1),
            ],
            'ZonedDateTime.Unit.Hour.DST.5' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(5),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.Unit.Minute' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.Unit.Second.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'ZonedDateTime.Unit.Second.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::of(seconds: 2),
                Precision::Second,
            ],
            'ZonedDateTime.Unit.Second.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::zero(),
                Precision::Minute,
            ],

            // ZonedDateTime vs LocalDateTime
            'ZonedDateTime.LocalDateTime.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 50, 5555),
                Duration::zero(),
            ],
            'ZonedDateTime.LocalDateTime.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 59, 5555),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.LocalDateTime.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.LocalDateTime.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.LocalDateTime.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.LocalDateTime.Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 50, 55, 5555),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.LocalDateTime.Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 15, 45, 55, 5555),
                Duration::of(hours: 3),
            ],
            'ZonedDateTime.LocalDateTime.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 16, 12, 45, 55, 5555),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.LocalDateTime.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2026, 2, 15, 12, 45, 55, 5555),
                Duration::of(days: 62),
            ],
            'ZonedDateTime.LocalDateTime.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::localOf(2006, 4, 2, 2, 0, 0, 0),
                Duration::ofHour(),
            ],
            'ZonedDateTime.LocalDateTime.DST.1' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::localOf(2006, 4, 2, 3, 0, 0, 0),
                Duration::ofHour(),
            ],
            'ZonedDateTime.LocalDateTime.DST.2' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::localOf(2006, 4, 2, 4, 0, 0, 0),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.LocalDateTime.DST.3' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                self::localOf(2006, 4, 2, 4, 0, 0, 0),
                Duration::ofHour(),
            ],

            // LocalDateTime vs ZonedDateTime
            'LocalDateTime.ZonedDateTime.Zero' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 50, 5555, '+01:00'),
                Duration::zero(),
            ],
            'LocalDateTime.ZonedDateTime.Seconds.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'LocalDateTime.ZonedDateTime.Seconds.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'LocalDateTime.ZonedDateTime.Seconds.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                Duration::of(seconds: 4),
            ],
            'LocalDateTime.ZonedDateTime.Seconds.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'LocalDateTime.ZonedDateTime.Minutes' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
            ],
            'LocalDateTime.ZonedDateTime.Hours' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 15, 45, 55, 5555, '+01:00'),
                Duration::of(hours: 3),
            ],
            'LocalDateTime.ZonedDateTime.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 16, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 1),
            ],
            'LocalDateTime.ZonedDateTime.Months' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2026, 2, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 62),
            ],
            'LocalDateTime.ZonedDateTime.DST.0' => [
                self::localOf(2006, 4, 2, 1, 0, 0, 0),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'LocalDateTime.ZonedDateTime.DST.1' => [
                self::localOf(2006, 4, 2, 2, 0, 0, 0),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                Duration::zero(),
            ],
            'LocalDateTime.ZonedDateTime.DST.2' => [
                self::localOf(2006, 4, 2, 3, 0, 0, 0),
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],

            // ZonedDateTime vs \DateTimeInterface
            'ZonedDateTime.Native.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-15 12:45:50.005555 +01:00'),
                Duration::zero(),
            ],
            'ZonedDateTime.Native.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.005555 +01:00'),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.Native.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.000000 +01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.Native.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.000000 +01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.Native.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:59.005555 +01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.Native.Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:50:55.005555 +01:00'),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.Native.Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-15 16:45:55.005555 +02:00'),
                Duration::of(hours: 3),
            ],
            'ZonedDateTime.Native.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-16 12:45:55.005555 +01:00'),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Native.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2026-02-15 14:45:55.005555 +03:00'),
                Duration::of(days: 62),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('untilProvider')]
    public function testUntil(
        DateTime $datetime,
        DateTime|Unit|\DateTimeInterface $end,
        Duration $expected,
        Precision $precision = Precision::Micro,
    ): void {
        $actual = $datetime->until($end, $precision);

        self::assertEquals($expected, $actual);
    }

    public static function differenceProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.LocalDateTime.Zero' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::zero(),
            ],
            'LocalDateTime.LocalDateTime.Seconds.0' => [
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'LocalDateTime.LocalDateTime.Seconds.1' => [
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'LocalDateTime.LocalDateTime.Seconds.2' => [
                self::localOf(2025, 12, 15, 12, 45, 59, 0),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::zero(),
                Precision::Minute,
            ],
            'LocalDateTime.LocalDateTime.Minutes.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 50, 55, 5555),
                Duration::of(minutes: 5),
            ],
            'LocalDateTime.LocalDateTime.Minutes.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 0),
                self::localOf(2025, 12, 15, 12, 50, 54, 5555),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'LocalDateTime.LocalDateTime.Hours' => [
                self::localOf(2025, 12, 15, 13, 45, 55, 5555),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(hours: 1),
            ],
            'LocalDateTime.LocalDateTime.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::localOf(2025, 12, 16, 12, 45, 55, 5555),
                Duration::of(days: 1),
            ],
            'LocalDateTime.Date.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date::of(2025, 12, 20),
                Duration::of(days: 5),
            ],
            'LocalDateTime.Date.Months' => [
                self::localOf(2026, 1, 15, 12, 45, 55, 5555),
                Date::of(2025, 12, 15),
                Duration::of(days: 31),
            ],
            'LocalDateTime.Unit.Year' => [
                self::localOf(2026, 12, 15, 12, 45, 55, 5555),
                Date\Year::of(2025),
                Duration::of(days: 365),
            ],
            'LocalDateTime.Unit.Month' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\Month::November,
                Duration::of(days: 30),
            ],
            'LocalDateTime.Unit.DayOfMonth' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'LocalDateTime.Unit.DayOfWeek' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'LocalDateTime.Unit.DayOfYear' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Date\DayOfYear::of(348),
                Duration::of(days: 1),
            ],
            'LocalDateTime.Time.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'LocalDateTime.Time.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'LocalDateTime.Time.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'LocalDateTime.Time.3' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time::of(12, 45, 59),
                Duration::zero(),
                Precision::Minute,
            ],
            'LocalDateTime.Unit.Hour' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'LocalDateTime.Unit.Minute' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'LocalDateTime.Unit.Second.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'LocalDateTime.Unit.Second.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57),
                Duration::of(seconds: 1, micros: 994_445),
            ],
            'LocalDateTime.Unit.Second.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Time\Second::of(57, 9999),
                Duration::zero(),
                Precision::Minute,
            ],

            // ZonedDateTime
            'ZonedDateTime.ZonedDateTime.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::zero(),
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.ZonedDateTime.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.ZonedDateTime.Minutes.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.ZonedDateTime.Minutes.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5, micros: 5555),
            ],
            'ZonedDateTime.ZonedDateTime.Minutes.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 0, '+01:00'),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'ZonedDateTime.ZonedDateTime.Hours' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+00:00'),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(hours: 1),
            ],
            'ZonedDateTime.ZonedDateTime.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::zonedOf(2025, 12, 16, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.ZonedDateTime.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'ZonedDateTime.ZonedDateTime.DST.1' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'ZonedDateTime.ZonedDateTime.DST.2' => [
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.ZonedDateTime.DST.3' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 1, 0, 0, 0, 'America/New_York'),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.ZonedDateTime.DST.4' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 0, 'America/New_York'),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
                Duration::ofHour(),
            ],
            'ZonedDateTime.Date.Days' => [
                self::zonedOf(2025, 12, 20, 12, 45, 55, 5555, '+01:00'),
                Date::of(2025, 12, 15),
                Duration::of(days: 5),
            ],
            'ZonedDateTime.Date.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date::of(2026, 01, 15),
                Duration::of(days: 31),
            ],
            'ZonedDateTime.Unit.Year' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Year::of(2026),
                Duration::of(days: 365),
            ],
            'ZonedDateTime.Unit.Month' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\Month::November,
                Duration::of(days: 30),
            ],
            'ZonedDateTime.Unit.DayOfMonth' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfMonth::of(16),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Unit.DayOfWeek' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfWeek::Tuesday,
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Unit.DayOfYear' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Date\DayOfYear::of(348),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Time.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, Second::of(59, 5555)),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.Time.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.Time.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.Time.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time::of(12, 45, 59),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.Unit.Hour' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Hour::of(15),
                Duration::of(hours: 3),
            ],
            'ZonedDateTime.Unit.Hour.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(2),
                Duration::ofHour(),
            ],
            'ZonedDateTime.Unit.Hour.DST.1' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(3),
                Duration::ofHour(),
            ],
            'ZonedDateTime.Unit.Hour.DST.2' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(4),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.Unit.Hour.DST.3' => [
                self::zonedOf(2006, 4, 2, 4, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(1),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.Unit.Hour.DST.4' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Time\Hour::of(5),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.Unit.Minute' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Minute::of(50),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.Unit.Second.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57, 5555),
                Duration::of(seconds: 2),
            ],
            'ZonedDateTime.Unit.Second.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::of(seconds: 1, micros: 994_445),
            ],
            'ZonedDateTime.Unit.Second.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Time\Second::of(57),
                Duration::of(seconds: 2),
                Precision::Second,
            ],

            // ZonedDateTime vs LocalDateTime
            'ZonedDateTime.LocalDateTime.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::zero(),
            ],
            'ZonedDateTime.LocalDateTime.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.LocalDateTime.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 3, micros: 994_445),
            ],
            'ZonedDateTime.LocalDateTime.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.LocalDateTime.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.LocalDateTime.Minutes.0' => [
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.LocalDateTime.Minutes.1' => [
                self::zonedOf(2025, 12, 15, 12, 50, 55, 0, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(minutes: 4, seconds: 59, micros: 994_445),
            ],
            'ZonedDateTime.LocalDateTime.Minutes.2' => [
                self::zonedOf(2025, 12, 15, 12, 50, 55, 0, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(minutes: 5),
                Precision::Minute,
            ],
            'ZonedDateTime.LocalDateTime.Hours' => [
                self::zonedOf(2025, 12, 15, 15, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                Duration::of(hours: 3),
            ],
            'ZonedDateTime.LocalDateTime.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2025, 12, 16, 12, 45, 55, 5555),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.LocalDateTime.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                self::localOf(2026, 2, 15, 12, 45, 55, 5555),
                Duration::of(days: 62),
            ],
            'ZonedDateTime.LocalDateTime.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 2, 45, 55, 5555),
                Duration::ofHour(),
            ],
            'ZonedDateTime.LocalDateTime.DST.1' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 3, 45, 55, 5555),
                Duration::ofHour(),
            ],
            'ZonedDateTime.LocalDateTime.DST.2' => [
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 4, 45, 55, 5555),
                Duration::of(hours: 2),
            ],
            'ZonedDateTime.LocalDateTime.DST.3' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 4, 45, 55, 5555),
                Duration::ofHour(),
            ],
            'ZonedDateTime.LocalDateTime.DST.4' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 2, 45, 55, 5555),
                Duration::zero(),
            ],
            'ZonedDateTime.LocalDateTime.DST.5' => [
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                self::localOf(2006, 4, 2, 1, 45, 55, 5555),
                Duration::ofHour(),
            ],

            // LocalDateTime vs ZonedDateTime
            'LocalDateTime.ZonedDateTime.Zero' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::zero(),
            ],
            'LocalDateTime.ZonedDateTime.Seconds.0' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                Duration::of(seconds: 4),
            ],
            'LocalDateTime.ZonedDateTime.Seconds.1' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'LocalDateTime.ZonedDateTime.Seconds.2' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 59, 0, '+01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'LocalDateTime.ZonedDateTime.Minutes' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 50, 55, 5555, '+01:00'),
                Duration::of(minutes: 5),
            ],
            'LocalDateTime.ZonedDateTime.Hours' => [
                self::localOf(2025, 12, 15, 15, 45, 55, 5555),
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(hours: 3),
            ],
            'LocalDateTime.ZonedDateTime.Days' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2025, 12, 16, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 1),
            ],
            'LocalDateTime.ZonedDateTime.Months' => [
                self::localOf(2025, 12, 15, 12, 45, 55, 5555),
                self::zonedOf(2026, 02, 15, 12, 45, 55, 5555, '+01:00'),
                Duration::of(days: 62),
            ],
            'LocalDateTime.ZonedDateTime.DST.0' => [
                self::localOf(2006, 4, 2, 2, 45, 55, 5555),
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Duration::zero(),
            ],
            'LocalDateTime.ZonedDateTime.DST.1' => [
                self::localOf(2006, 4, 2, 1, 45, 55, 5555),
                self::zonedOf(2006, 4, 2, 3, 45, 55, 5555, 'America/New_York'),
                Duration::ofHour(),
            ],
            'LocalDateTime.ZonedDateTime.DST.2' => [
                self::localOf(2006, 4, 2, 3, 45, 55, 5555),
                self::zonedOf(2006, 4, 2, 1, 45, 55, 5555, 'America/New_York'),
                Duration::ofHour(),
            ],

            // ZonedDateTime vs \DateTimeInterface
            'ZonedDateTime.Native.Zero' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-15 12:45:55.005555 +01:00'),
                Duration::zero(),
            ],
            'ZonedDateTime.Native.Seconds.0' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.005555 +01:00'),
                Duration::of(seconds: 4),
            ],
            'ZonedDateTime.Native.Seconds.1' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.000000 +01:00'),
                Duration::of(seconds: 4, micros: 5555),
            ],
            'ZonedDateTime.Native.Seconds.2' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.000000 +01:00'),
                Duration::of(seconds: 4),
                Precision::Second,
            ],
            'ZonedDateTime.Native.Seconds.3' => [
                self::zonedOf(2025, 12, 15, 12, 45, 59, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:45:55.000000 +01:00'),
                Duration::zero(),
                Precision::Minute,
            ],
            'ZonedDateTime.Native.Minutes' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2025-12-15 12:50:55.005555 +01:00'),
                Duration::of(minutes: 5),
            ],
            'ZonedDateTime.Native.Hours' => [
                self::zonedOf(2025, 12, 15, 16, 45, 55, 5555, '+02:00'),
                new \DateTime('2025-12-15 12:45:55.005555 +01:00'),
                Duration::of(hours: 3),
            ],
            'ZonedDateTime.Native.Days' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTime('2025-12-16 12:45:55.005555 +01:00'),
                Duration::of(days: 1),
            ],
            'ZonedDateTime.Native.Months' => [
                self::zonedOf(2025, 12, 15, 12, 45, 55, 5555, '+01:00'),
                new \DateTimeImmutable('2026-02-15 14:45:55.005555 +03:00'),
                Duration::of(days: 62),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('differenceProvider')]
    public function testDifference(
        DateTime $datetime,
        DateTime|Unit|\DateTimeInterface $end,
        Duration $expected,
        Precision $precision = Precision::Micro,
    ): void {
        $actual = $datetime->difference($end, $precision);

        self::assertEquals($expected, $actual);
    }

    public static function withProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.Date' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date::of(1834, 6, 25),
            ],
            'LocalDateTime.Year' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\Year::of(1999),
            ],
            'LocalDateTime.Month' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\Month::November,
            ],
            'LocalDateTime.DayOfMonth.14' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfMonth::of(14),
            ],
            'LocalDateTime.DayOfMonth.31' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfMonth::of(31),
            ],
            'LocalDateTime.DayOfWeek.Monday' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfWeek::Monday,
            ],
            'LocalDateTime.DayOfWeek.Wednesday' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfWeek::Wednesday,
            ],
            'LocalDateTime.DayOfWeek.Sunday' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfWeek::Sunday,
            ],
            'LocalDateTime.DayOfYear.First' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfYear::first(),
            ],
            'LocalDateTime.DayOfYear.Last.NonLeap' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Date\DayOfYear::last(),
            ],
            'LocalDateTime.DayOfYear.Last.Leap' => [
                self::localOf(2024, 3, 24, 23, 59, 59, 999999),
                Date\DayOfYear::last(),
            ],
            'LocalDateTime.Time' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time::midnight(),
            ],
            'LocalDateTime.Hour' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Hour::of(14),
            ],
            'LocalDateTime.Minute' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Minute::of(30),
            ],
            'LocalDateTime.Second.45' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Second::of(45),
            ],
            'LocalDateTime.Second.15.008765' => [
                self::localOf(2025, 3, 24, 23, 59, 59, 999999),
                Time\Second::of(15, 8765),
            ],

            // ZonedDateTime
            'ZonedDateTime.Date' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date::of(1834, 6, 25),
            ],
            'ZonedDateTime.Year' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\Year::of(1999),
            ],
            'ZonedDateTime.Month' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\Month::November,
            ],
            'ZonedDateTime.DayOfMonth.14' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfMonth::of(14),
            ],
            'ZonedDateTime.DayOfMonth.31' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfMonth::of(31),
            ],
            'ZonedDateTime.DayOfWeek.Monday' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfWeek::Monday,
            ],
            'ZonedDateTime.DayOfWeek.Wednesday' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfWeek::Wednesday,
            ],
            'ZonedDateTime.DayOfWeek.Sunday' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfWeek::Sunday,
            ],
            'ZonedDateTime.DayOfYear.First' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfYear::first(),
            ],
            'ZonedDateTime.DayOfYear.Last.NonLeap' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfYear::last(),
            ],
            'ZonedDateTime.DayOfYear.Last.Leap' => [
                self::zonedOf(2024, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Date\DayOfYear::last(),
            ],
            'ZonedDateTime.Time' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time::midnight(),
            ],
            'ZonedDateTime.Hour' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Hour::of(14),
            ],
            'ZonedDateTime.Minute' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Minute::of(30),
            ],
            'ZonedDateTime.Second.45' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Second::of(45),
            ],
            'ZonedDateTime.Second.15.008765' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                Time\Second::of(15, 8765),
            ],
            'ZonedDateTime.Timezone.UTC' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                new \DateTimeZone('UTC'),
            ],
            'ZonedDateTime.Timezone.+08:30' => [
                self::zonedOf(2025, 3, 24, 23, 59, 59, 999999, '+01:00'),
                new \DateTimeZone('+08:30'),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(DateTime $datetime, Unit|\DateTimeZone $unit): void
    {
        $result = $datetime->with($unit);

         if ($unit instanceof Date) {
            self::assertSame($unit, $result->date());
            self::assertSame($datetime->time(), $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof Date\DateUnit) {
            self::assertSame($datetime->date()->with($unit), $result->date());
            self::assertSame($datetime->time(), $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof Time) {
            self::assertSame($datetime->date(), $result->date());
            self::assertSame($unit, $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof Time\TimeUnit) {
            self::assertSame($datetime->date(), $result->date());
            self::assertSame($datetime->time()->with($unit), $result->time());
            if ($datetime instanceof ZonedDateTime && $result instanceof ZonedDateTime) {
                self::assertEquals($datetime->timezone()->getName(), $result->timezone()->getName());
            }
        } elseif ($unit instanceof \DateTimeZone) {
            self::assertSame($datetime->date(), $result->date());
            self::assertSame($datetime->time(), $result->time());
            if ($result instanceof ZonedDateTime) {
                self::assertEquals($unit->getName(), $result->timezone()->getName());
            }
        }
    }

    public static function withAndDstProvider(): array
    {
        return [
            'Hour.0' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 999999, 'America/New_York'),
                Time\Hour::of(2),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'DayOfMonth.0' => [
                self::zonedOf(2006, 4, 1, 2, 59, 59, 999999, 'America/New_York'),
                Date\DayOfMonth::of(2),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Month.0' => [
                self::zonedOf(2006, 3, 2, 2, 59, 59, 999999, 'America/New_York'),
                Date\Month::of(4),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Year.0' => [
                self::zonedOf(2005, 4, 2, 2, 59, 59, 999999, 'America/New_York'),
                Date\Year::of(2006),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Date.0' => [
                self::zonedOf(2005, 11, 23, 2, 59, 59, 999999, 'America/New_York'),
                Date::of(2006, 4, 2),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 999999, 'America/New_York'),
            ],
            'Time.0' => [
                self::zonedOf(2006, 4, 2, 12, 59, 59, 999999, 'America/New_York'),
                Time::of(2, 12, 45),
                self::zonedOf(2006, 4, 2, 3, 12, 45, 0, 'America/New_York'),
            ],
        ];
    }

    #[Depends('testWith')]
    #[DataProvider('withAndDstProvider')]
    public function testWithAndDst(DateTime $datetime, Unit|\DateTimeZone $unit, DateTime $expected): void
    {
        self::assertEquals($expected, $datetime->with($unit));
    }

    public static function addProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.Duration.Zero' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::zero(),
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
            ],
            'LocalDateTime.Duration.Microseconds' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 500_000),
                Duration::of(micros: 505_555),
                self::localOf(2025, 12, 30, 12, 15, 31, 5555),
            ],
            'LocalDateTime.Duration.Seconds' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(seconds: 5),
                self::localOf(2025, 12, 30, 12, 15, 35, 5555),
            ],
            'LocalDateTime.Duration.Minutes' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(minutes: 65),
                self::localOf(2025, 12, 30, 13, 20, 30, 5555),
            ],
            'LocalDateTime.Duration.Hours.0' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(hours: 12, minutes: 30),
                self::localOf(2025, 12, 31, 0, 45, 30, 5555),
            ],
            'LocalDateTime.Duration.Hours.1' => [
                self::localOf(2006, 4, 2, 1, 15, 30, 5555),
                Duration::of(hours: 1, minutes: 30),
                self::localOf(2006, 4, 2, 2, 45, 30, 5555),
            ],
            'LocalDateTime.Duration.Days' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::localOf(2026, 1, 1, 0, 45, 30, 5555),
            ],
            'LocalDateTime.DateInterval' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::localOf(2026, 1, 1, 0, 45, 30, 5565),
            ],

            // ZonedDateTime
            'ZonedDateTime.Duration.Zero' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::zero(),
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Microseconds' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 500_000, '+01:00'),
                Duration::of(micros: 505_555),
                self::zonedOf(2025, 12, 30, 12, 15, 31, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Seconds' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(seconds: 5, micros: 1),
                self::zonedOf(2025, 12, 30, 12, 15, 35, 5556, '+01:00'),
            ],
            'ZonedDateTime.Duration.Minutes' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(minutes: 65),
                self::zonedOf(2025, 12, 30, 13, 20, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Hours' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(hours: 12, minutes: 30),
                self::zonedOf(2025, 12, 31, 0, 45, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Days' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::zonedOf(2026, 1, 1, 0, 45, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.DateInterval' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::zonedOf(2026, 1, 1, 0, 45, 30, 5565, '+01:00'),
            ],
            'ZonedDateTime.DST.Duration.Microseconds' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(micros: 500_000),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 0, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.0' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(hours: 1),
                self::zonedOf(2006, 4, 2, 3, 59, 59, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.1' => [
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(hours: 2),
                self::zonedOf(2006, 4, 2, 4, 59, 59, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.2' => [
                self::zonedOf(2006, 4, 1, 1, 59, 59, 500_000, 'America/New_York'),
                Duration::of(hours: 26),
                self::zonedOf(2006, 4, 2, 4, 59, 59, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.3' => [
                self::zonedOf(2006, 4, 2, 0, 59, 59, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.4' => [
                self::zonedOf(2006, 4, 2, 3, 59, 59, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 4, 59, 59, 500_000, 'America/New_York'),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('addProvider')]
    public function testAdd(DateTime $datetime, Duration|\DateInterval $duration, DateTime $expected): void
    {
        $actual = $datetime->add($duration);

        self::assertEquals($expected, $actual);
    }

    public static function subProvider(): array
    {
        return [
            // LocalDateTime
            'LocalDateTime.Duration.Zero' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::zero(),
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
            ],
            'LocalDateTime.Duration.Microseconds.0' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(micros: 6555),
                self::localOf(2025, 12, 30, 12, 15, 29, 999_000),
            ],
            'LocalDateTime.Duration.Microseconds.1' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                Duration::of(micros: 1),
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
            ],
            'LocalDateTime.Duration.Seconds' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(seconds: 5, micros: 1),
                self::localOf(2025, 12, 30, 12, 15, 25, 5554),
            ],
            'LocalDateTime.Duration.Minutes' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(minutes: 65),
                self::localOf(2025, 12, 30, 11, 10, 30, 5555),
            ],
            'LocalDateTime.Duration.Hours' => [
                self::localOf(2025, 12, 30, 12, 15, 30, 5555),
                Duration::of(hours: 12, minutes: 30),
                self::localOf(2025, 12, 29, 23, 45, 30, 5555),
            ],
            'LocalDateTime.Duration.Days' => [
                self::localOf(2026, 1, 1, 12, 15, 30, 5555),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::localOf(2025, 12, 30, 23, 45, 30, 5555),
            ],
            'LocalDateTime.DateInterval' => [
                self::localOf(2026, 1, 1, 12, 15, 30, 5555),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::localOf(2025, 12, 30, 23, 45, 30, 5545),
            ],

            // ZonedDateTime
            'ZonedDateTime.Duration.Zero' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::zero(),
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Microseconds.0' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(micros: 6555),
                self::zonedOf(2025, 12, 30, 12, 15, 29, 999_000, '+01:00'),
            ],
            'ZonedDateTime.Duration.Microseconds.1' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, '+01:00'),
                Duration::of(micros: 1),
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, '+01:00'),
            ],
            'ZonedDateTime.Duration.Seconds' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(seconds: 5, micros: 1),
                self::zonedOf(2025, 12, 30, 12, 15, 25, 5554, '+01:00'),
            ],
            'ZonedDateTime.Duration.Minutes' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(minutes: 65),
                self::zonedOf(2025, 12, 30, 11, 10, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Hours' => [
                self::zonedOf(2025, 12, 30, 12, 15, 30, 5555, '+01:00'),
                Duration::of(hours: 12, minutes: 30),
                self::zonedOf(2025, 12, 29, 23, 45, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.Duration.Days' => [
                self::zonedOf(2026, 1, 1, 12, 15, 30, 5555, '+01:00'),
                Duration::of(days: 1, hours: 12, minutes: 30),
                self::zonedOf(2025, 12, 30, 23, 45, 30, 5555, '+01:00'),
            ],
            'ZonedDateTime.DateInterval' => [
                self::zonedOf(2026, 1, 1, 12, 15, 30, 5555, '+01:00'),
                \DateInterval::createFromDateString('1 day, 12 hours, 30 minutes, 0 seconds, 10 microseconds'),
                self::zonedOf(2025, 12, 30, 23, 45, 30, 5545, '+01:00'),
            ],
            'ZonedDateTime.DST.Duration.Microseconds' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 50_000, 'America/New_York'),
                Duration::of(micros: 550_000),
                self::zonedOf(2006, 4, 2, 1, 59, 59, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.0' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 3, 0, 0, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.1' => [
                self::zonedOf(2006, 4, 2, 3, 0, 0, 500_000, 'America/New_York'),
                Duration::ofHour(),
                self::zonedOf(2006, 4, 2, 1, 0, 0, 500_000, 'America/New_York'),
            ],
            'ZonedDateTime.DST.Duration.Hours.2' => [
                self::zonedOf(2006, 4, 2, 4, 0, 0, 500_000, 'America/New_York'),
                Duration::of(hours: 2),
                self::zonedOf(2006, 4, 2, 1, 0, 0, 500_000, 'America/New_York'),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DependsOnClass(DurationTest::class)]
    #[DataProvider('subProvider')]
    public function testSub(DateTime $datetime, Duration|\DateInterval $duration, DateTime $expected): void
    {
        $actual = $datetime->sub($duration);

        self::assertEquals($expected, $actual);
    }

    public static function instantProvider(): array
    {
        return [
            'LocalDateTime.0' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 0),
                Instant::of(second: 0),
            ],
            'LocalDateTime.1' => [
                self::localOf(1970, 1, 1, 0, 0, 0, 1),
                Instant::of(second: 0, micro: 1),
            ],
            'LocalDateTime.2' => [
                self::localOf(1970, 1, 1, 0, 0, 1, 1),
                Instant::of(second: 1, micro: 1),
            ],
            'LocalDateTime.3' => [
                self::localOf(1969, 12, 31, 23, 59, 59, 999_999),
                Instant::of(second: -1, micro: 999_999),
            ],
            'LocalDateTime.4' => [
                self::localOf(1969, 12, 31, 23, 59, 58, 550_000),
                Instant::of(second: -2, micro: 550_000),
            ],
            'ZonedDateTime.0' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, '+00:00'),
                Instant::of(second: 0),
            ],
            'ZonedDateTime.1' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 1, '+00:00'),
                Instant::of(second: 0, micro: 1),
            ],
            'ZonedDateTime.2' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 0, '+01:00'),
                Instant::of(second: -3600),
            ],
            'ZonedDateTime.3' => [
                self::zonedOf(1970, 1, 1, 0, 0, 0, 1, '+01:00'),
                Instant::of(second: -3600, micro: 1),
            ],
            'ZonedDateTime.4' => [
                self::zonedOf(1970, 1, 1, 0, 0, 1, 1, '+02:00'),
                Instant::of(second: -7199, micro: 1),
            ],
            'ZonedDateTime.5' => [
                self::zonedOf(1969, 12, 31, 23, 59, 59, 999_999, '+01:00'),
                Instant::of(second: -3601, micro: 999_999),
            ],
            'ZonedDateTime.6' => [
                self::zonedOf(1969, 12, 31, 23, 59, 58, 550_000, '+02:00'),
                Instant::of(second: -7202, micro: 550_000),
            ],
            'ZonedDateTime.DST.0' => [
                self::zonedOf(2006, 4, 2, 1, 15, 58, 550_000, 'America/New_York'),
                Instant::of(second: 1143958558, micro: 550_000),
            ],
            'ZonedDateTime.DST.1' => [
                self::zonedOf(2006, 4, 2, 3, 15, 58, 550_000, 'America/New_York'),
                Instant::of(second: 1143962158, micro: 550_000),
            ],
            'ZonedDateTime.DST.2' => [
                self::zonedOf(2006, 4, 2, 2, 15, 58, 550_000, 'America/New_York'),
                Instant::of(second: 1143962158, micro: 550_000),
            ],
        ];
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('instantProvider')]
    public function testInstant(DateTime $datetime, Instant $expected): void
    {
        self::assertEquals($expected, $datetime->instant());
    }

    private static function localOf(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        int $second,
        int $micro,
    ): LocalDateTime {
        return LocalDateTime::of(Date::of($year, $month, $day), Time::of($hour, $minute, Second::of($second, $micro)));
    }

    private static function zonedOf(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        int $second,
        int $micro,
        string $timezone,
    ): ZonedDateTime {
        return ZonedDateTime::ofLocal(
            self::localOf($year, $month, $day, $hour, $minute, $second, $micro),
            new \DateTimeZone($timezone),
        );
    }
}
