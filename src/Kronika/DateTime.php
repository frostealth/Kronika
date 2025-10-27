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

use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Utils\Compared;

/**
 * Represents a date-time.
 */
interface DateTime
{
    /**
     * Returns an instance of Date from this date-time.
     */
    public function date(): Date;

    /**
     * Returns an instance of Year from this date-time.
     */
    public function year(): Year;

    /**
     * Returns an instance of Month from this date-time.
     */
    public function month(): Month;

    /**
     * Returns an instance of DayOfMonth from this date-time.
     */
    public function day(): DayOfMonth;

    /**
     * Returns an instance of DayOfWeek from this date-time.
     */
    public function dayOfWeek(): DayOfWeek;

    /**
     * Returns an instance of Time from this date-time.
     */
    public function time(): Time;

    /**
     * Returns an instance of Hour from this date-time.
     */
    public function hour(): Hour;

    /**
     * Returns an instance of Minute from this date-time.
     */
    public function minute(): Minute;

    /**
     * Returns an instance of Second from this date-time.
     */
    public function second(): Second;

    /**
     * Returns an instance of DateTime with a given date-time unit.
     *
     * If the day of the month of the resulting date-time is greater than
     * the length of the month, then the last day of the month will be set.
     *
     * ```
     * // 2025-11-29 12:15:30
     * $this->with(Time::of(10, 45, 50));   // 2025-11-29 10:45:50
     * $this->with(Minute::of(30));         // 2025-11-29 12:30:30
     * $this->with(Hour::of(21));           // 2025-11-29 21:15:30
     * $this->with(Second::of(10));         // 2025-11-29 12:15:10
     * $this->with(Date::of(1990, 01, 01)); // 1990-01-01 12:15:30
     * $this->with(Year::of(1990));         // 1990-11-29 12:15:30
     * $this->with($this->year()->next());  // 2026-11-29 12:15:30
     * $this->with(Month::January);         // 2025-01-29 12:15:30
     * $this->with(Month::February);        // 2025-02-28 12:15:30
     * $this->with(DayOfMonth::of(15));     // 2025-11-15 12:15:30
     * $this->with(DayOfMonth::of(31));     // 2025-11-30 12:15:30
     * $this->with(DayOfWeek::Monday);      // 2025-11-24 12:15:30
     * ```
     */
    public function with(Unit $unit): static;

    /**
     * Resets a microsecond to 0.
     *
     * ```
     * // 2025-12-31 10:15:30.999999
     * $datetime->resetMicro();  // 2025-12-31 10.15.30.000000
     * ```
     */
    public function resetMicro(): static;

    /**
     * Resets a second and microsecond to 0.
     *
     * ```
     * // 2025-12-31 10:15:30.999999
     * $datetime->resetSecond();  // 2025-12-31 10:15:00.000000
     * ```
     */
    public function resetSecond(): static;

    /**
     * Adds an amount of days, hours, minutes and seconds to this date-time.
     *
     * ```
     * // 2025-12-31 10:15:30 + 2 days and 45 minutes
     * $this->add(Duration::of(days: 2, minutes: 45));  // 2026-01-02 11:00:30
     * ```
     */
    public function add(Duration $interval): static;

    /**
     * Subtracts an amount of days, hours, minutes and seconds to this date-time.
     *
     * ```
     * // 2025-12-31 10:15:30 - 2 days and 45 minutes
     * $this->add(Duration::of(days: 2, minutes: 45));  // 2025-12-29 09:30:30
     * ```
     */
    public function sub(Duration $interval): static;

    /**
     * Returns an instance of Duration from this date-time to another.
     *
     * ```
     * // 2025-12-10 10:15:30 vs 2025-12-20 12:30:45
     * $duration = $this->until($other);
     * $duration->days();    // 10
     * $duration->hours();   // 2
     * $duration->minutes(); // 15
     * $duration->seconds(); // 15
     *
     * // 2025-12-10 10:15:30 vs 2025-11-01 00:00:00
     * $duration = $this->until($other);
     * $duration->days();    // 0
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     *
     * // ZonedDateTime vs LocalDateTime
     * // 2025-12-10 10:15:30 +01:00 vs 2025-12-20 12:30:45
     * $duration = $this->until($other);
     * $duration->days();    // 10
     * $duration->hours();   // 2
     * $duration->minutes(); // 15
     * $duration->seconds(); // 15
     * ```
     */
    public function until(self $end): Duration;

    /**
     * Checks if this date-time is before another.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isBefore($other);  // true
     * $this->isBefore($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->isBefore($other, Precision::Second);  // true
     * $this->isBefore($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->isBefore($other, Precision::Minute);  // false
     * ```
     */
    public function isBefore(DateTime $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time is before or equal to another.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isBeforeOrEqual($other);  // true
     *
     * // 2025-12-31 10:15:30.999999 vs 2025-12-31 10:15:30.000000
     * $this->isBeforeOrEqual($other);  // false
     * $this->isBeforeOrEqual($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:59.999999 vs 2025-12-31 10:15:00.000000
     * $this->isBeforeOrEqual($other, Precision::Second);  // false
     * $this->isBeforeOrEqual($other, Precision::Minute);  // true
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:00.000000
     * $this->isBeforeOrEqual($other, Precision::Minute);  // false
     * ```
     */
    public function isBeforeOrEqual(DateTime $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time is equal to another.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isEqualTo($other);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isEqualTo($other);  // false
     * $this->isEqualTo($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->isEqualTo($other, Precision::Second);  // false
     * $this->isEqualTo($other, Precision::Minute);  // true
     *
     * // 2025-12-31 10:30:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->isEqualTo($other, Precision::Minute);  // false
     * ```
     */
    public function isEqualTo(DateTime $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time is not equal to another.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isNotEqualTo($other);  // true
     * $this->isNotEqualTo($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->isNotEqualTo($other, Precision::Second);  // true
     * $this->isNotEqualTo($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->isNotEqualTo($other, Precision::Minute);  // true
     * ```
     */
    public function isNotEqualTo(DateTime $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time is after or equal to another.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isAfterOrEqual($other);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isAfterOrEqual($other);  // false
     * $this->isAfterOrEqual($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqual($other, Precision::Second);  // false
     * $this->isAfterOrEqual($other, Precision::Minute);  // true
     *
     * // 1990-01-01 23:59:59.999999 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqual($other, Precision::Minute);  // false
     * ```
     */
    public function isAfterOrEqual(DateTime $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if date-this time is after another.
     *
     * ```
     * // 2025-12-31 10:15:30.999999 vs 2025-12-31 10:15:30.000000
     * $this->isAfter($other);  // true
     * $this->isAfter($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:59.999999 vs 2025-12-31 10:15:30.000000
     * $this->isAfter($other, Precision::Second);  // true
     * $this->isAfter($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:30.000000
     * $this->isAfter($other, Precision::Minute);  // true
     * ```
     */
    public function isAfter(DateTime $other, Precision $precision = Precision::Micro): bool;

    /**
     * Compares this time with another.
     *
     * ```
     * // 2025-12-31 12:15:30 vs 2025-12-31 12:15:45
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     * $this->compareTo($other, Precision::Minute)->equal();  // true
     * ```
     */
    public function compareTo(DateTime $other, Precision $precision = Precision::Micro): Compared;

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string;

    /**
     * Returns an instance of \DateTimeImmutable with this date-time.
     */
    public function toNative(): \DateTimeImmutable;

    /**
     * Returns an instance of \DateTime with this date-time.
     */
    public function toNativeMutable(): \DateTime;

    /**
     * Returns an instance of "Kronika\Instant" with this date-time.
     */
    public function instant(): Instant;

    /** @return non-empty-string */
    public function __toString(): string;
}
