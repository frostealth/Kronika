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

namespace Kronika;

use Kronika\Tests\LocalDateTimeTest;
use Kronika\Tests\ZonedDateTimeTest;
use Kronika\Time\Second;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\TestCase;

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
        ];
    }

    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(DateTime $a, DateTime|\DateTimeInterface $b, Precision $precision, int $expected): void
    {
        $this->assertFalse($a->isBefore($a, $precision));
        $this->assertTrue($a->isBeforeOrEqual($a, $precision));
        $this->assertTrue($a->isEqualTo($a, $precision));
        $this->assertFalse($a->isNotEqualTo($a, $precision));
        $this->assertTrue($a->isAfterOrEqual($a, $precision));
        $this->assertFalse($a->isAfter($a, $precision));

        $comparison = $a->compareTo($b, $precision);
        $this->assertEquals($expected, $comparison->value());
        $this->assertEquals($comparison->less(), $a->isBefore($b, $precision));
        $this->assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqual($b, $precision));
        $this->assertEquals($comparison->greater(), $a->isAfter($b, $precision));
        $this->assertEquals($comparison->equal(), $a->isEqualTo($b, $precision));
        $this->assertEquals($comparison->notEqual(), $a->isNotEqualTo($b, $precision));
        $this->assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqual($b, $precision));
        $this->assertEquals($comparison->notEqual(), $a->isNotEqualTo($b, $precision));
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

        $this->assertSame($datetime->date(), $result->date());
        $this->assertSame($datetime->hour(), $result->hour());
        $this->assertSame($datetime->minute(), $result->minute());
        $this->assertEquals($datetime->second()->second(), $result->second()->second());
        $this->assertEquals(0, $result->second()->microsecond());
    }

    #[DependsExternal(ZonedDateTimeTest::class, 'testBasic')]
    #[DependsExternal(LocalDateTimeTest::class, 'testBasic')]
    #[DataProvider('resetProvider')]
    public function testResetSecond(DateTime $datetime): void
    {
        $result = $datetime->resetSecond();

        $this->assertSame($datetime->date(), $result->date());
        $this->assertSame($datetime->hour(), $result->hour());
        $this->assertSame($datetime->minute(), $result->minute());
        $this->assertEquals(0, $result->second()->second());
        $this->assertEquals(0, $result->second()->microsecond());
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
