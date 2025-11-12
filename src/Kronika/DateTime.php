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
use Kronika\Date\DayOfYear;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Exception\FormatError;
use Kronika\Format\DateTime\Formatter;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Utils\Compared;

/**
 * Represents a date-time.
 */
interface DateTime extends \Stringable
{
    /**
     * Returns an instance of `Date` from this date-time.
     */
    public function date(): Date;

    /**
     * Returns an instance of `Year` from this date-time.
     */
    public function year(): Year;

    /**
     * Returns an instance of `Month` from this date-time.
     */
    public function month(): Month;

    /**
     * Returns an instance of `DayOfMonth` from this date-time.
     */
    public function day(): DayOfMonth;

    /**
     * Returns an instance of `DayOfWeek` from this date-time.
     */
    public function dayOfWeek(): DayOfWeek;

    /**
     * Returns an instance of `DayOfYear` from this date-time.
     */
    public function dayOfYear(): DayOfYear;

    /**
     * Returns an instance of `Time` from this date-time.
     */
    public function time(): Time;

    /**
     * Returns an instance of `Hour` from this date-time.
     */
    public function hour(): Hour;

    /**
     * Returns an instance of `Minute` from this date-time.
     */
    public function minute(): Minute;

    /**
     * Returns an instance of `Second` from this date-time.
     */
    public function second(): Second;

    /**
     * Returns an instance of `DateTime` with a given date-time unit.
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
     *
     * $this->with(Date::of(1990, 01, 01)); // 1990-01-01 12:15:30
     * $this->with(Year::of(1990));         // 1990-11-29 12:15:30
     * $this->with($this->year()->next());  // 2026-11-29 12:15:30
     * $this->with(Month::January);         // 2025-01-29 12:15:30
     * $this->with(Month::February);        // 2025-02-28 12:15:30
     * $this->with(DayOfMonth::of(15));     // 2025-11-15 12:15:30
     * $this->with(DayOfMonth::of(31));     // 2025-11-30 12:15:30
     * $this->with(DayOfWeek::Monday);      // 2025-11-24 12:15:30
     * ```
     * ```
     * // 2025-11-29 12:15:30 vs DayOfMonth::of(31)
     * $this->with($day, rolling: false); // 2025-11-30 12:15:30
     * $this->with($day, rolling: true);  // 2025-12-01 12:15:30
     * ```
     *
     * @see \Kronika\Date – change only date
     * @see \Kronika\Date\Year – change only year
     * @see \Kronika\Date\Month – change only month
     * @see \Kronika\Date\DayOfMonth – change only day
     * @see \Kronika\Date\DayOfWeek – change/shift only day of week
     * @see \Kronika\Date\DayOfYear – change/shift only day of year
     * @see \Kronika\Time – change only time
     * @see \Kronika\Time\Hour – change only hour
     * @see \Kronika\Time\Minute – change only minute
     * @see \Kronika\Time\Second – change only second with microsecond
     */
    public function with(Unit $unit, bool $rolling = false): static;

    /**
     * Resets the microsecond to 0.
     *
     * ```
     * // 2025-12-31 10:15:30.999999
     * $datetime->resetMicro();  // 2025-12-31 10.15.30.000000
     * ```
     */
    public function resetMicro(): static;

    /**
     * Resets the second and microsecond to 0.
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
     * Subtracts an amount of days, hours, minutes and seconds from this date-time.
     *
     * ```
     * // 2025-12-31 10:15:30 - 2 days and 45 minutes
     * $this->add(Duration::of(days: 2, minutes: 45));  // 2025-12-29 09:30:30
     * ```
     */
    public function sub(Duration $interval): static;

    /**
     * Calculates the duration from this date-time or its unit to another one.
     *
     * ```
     * // 2025-12-10 10:15:30 vs 2025-12-20 12:30:45
     * $duration = $this->until($other);
     * $duration->days();    // 10
     * $duration->hours();   // 2
     * $duration->minutes(); // 15
     * $duration->seconds(); // 15
     * ```
     * ```
     * // 2025-12-10 10:15:30 vs 2025-11-01 00:00:00
     * $duration = $this->until($other);
     * $duration->days();    // 0
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     * ```
     * // DateTime vs Date
     * // 2025-12-10 10:15:30 vs Date::of(2025, 12, 20)
     * $duration = $this->until($other);
     * $duration->days();    // 10
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     *
     * @see \Kronika\Date
     * @see \Kronika\Date\Year
     * @see \Kronika\Date\Month
     * @see \Kronika\Date\DayOfMonth
     * @see \Kronika\Date\DayOfWeek
     * @see \Kronika\Date\DayOfYear
     * @see \Kronika\Time
     * @see \Kronika\Time\Hour
     * @see \Kronika\Time\Minute
     * @see \Kronika\Time\Second
     */
    public function until(self|Unit $end): Duration;

    /**
     * Calculates the duration between this date-time or its unit and another one.
     *
     * ```
     * // 2025-12-10 10:15:30 vs 2025-12-20 12:30:45
     * $duration = $this->difference($other);
     * $duration->days();    // 10
     * $duration->hours();   // 2
     * $duration->minutes(); // 15
     * $duration->seconds(); // 15
     * ```
     * ```
     * // 2025-12-10 10:15:30 vs 2025-11-01 00:00:00
     * $duration = $this->difference($other);
     * $duration->days();    // 0
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     * ```
     * // DateTime vs Date
     * // 2025-12-10 10:15:30 vs Date::of(2025, 12, 20)
     * $duration = $this->difference($other);
     * $duration->days();    // 10
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     *
     * @see \Kronika\Date
     * @see \Kronika\Date\Year
     * @see \Kronika\Date\Month
     * @see \Kronika\Date\DayOfMonth
     * @see \Kronika\Date\DayOfWeek
     * @see \Kronika\Date\DayOfYear
     * @see \Kronika\Time
     * @see \Kronika\Time\Hour
     * @see \Kronika\Time\Minute
     * @see \Kronika\Time\Second
     */
    public function difference(self|Unit $other): Duration;

    /**
     * Checks if this date-time or its unit is equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->is($other);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->is($other);  // false
     * $this->is($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->is($other, Precision::Second);  // false
     * $this->is($other, Precision::Minute);  // true
     *
     * // 2025-12-31 10:30:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->is($other, Precision::Minute);  // false
     *
     * // 2025-12-31 10:30:00.000000 vs Date::of(2025, 12, 31)
     * $this->is($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function is(self|Unit $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time or its unit is not equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isNot($other);  // true
     * $this->isNot($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->isNot($other, Precision::Second);  // true
     * $this->isNot($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->isNot($other, Precision::Minute);  // true
     *
     * // 2026-01-01 00:00:00.000000 vs Date::of(2025, 12, 31)
     * $this->isNot($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isNot(self|Unit $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time or its unit is before another one.
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
     *
     * // 2026-01-01 00:00:00.000000 vs Date::of(2025, 12, 31)
     * $this->isBefore($other);  // false
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isBefore(self|Unit $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time or its unit is before or equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 2025-12-31 10:15:30.999999 vs 2025-12-31 10:15:30.000000
     * $this->isBeforeOrEqualTo($other);  // false
     * $this->isBeforeOrEqualTo($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:59.999999 vs 2025-12-31 10:15:00.000000
     * $this->isBeforeOrEqualTo($other, Precision::Second);  // false
     * $this->isBeforeOrEqualTo($other, Precision::Minute);  // true
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:00.000000
     * $this->isBeforeOrEqualTo($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs Date::of(2025, 12, 31)
     * $this->isBeforeOrEqualTo($other);  // false
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isBeforeOrEqualTo(self|Unit $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time or its unit is after or equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isAfterOrEqualTo($other);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isAfterOrEqualTo($other);  // false
     * $this->isAfterOrEqualTo($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqualTo($other, Precision::Second);  // false
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // true
     *
     * // 1990-01-01 23:59:59.999999 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // false
     *
     * // 1990-01-01 23:59:59.999999 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // false
     *
     * // 1990-01-01 23:59:59.999999 vs Time::of(23, 59, 59)
     * $this->isAfterOrEqualTo($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isAfterOrEqualTo(self|Unit $other, Precision $precision = Precision::Micro): bool;

    /**
     * Checks if this date-time or its unit is after another one.
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
     *
     * // 2026-01-01 10:15:55.000000 vs Time::of(10, 15, 30)
     * $this->isAfter($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isAfter(self|Unit $other, Precision $precision = Precision::Micro): bool;

    /**
     * Compares this date-time or its unit to another one.
     *
     * ```
     * // 2025-12-31 12:15:30 vs 2025-12-31 12:15:45
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     * $this->compareTo($other, Precision::Minute)->equal();  // true
     *
     * // 2025-12-31 12:15:30 vs Date::of(2025, 12, 31)
     * $this->compareTo($other)->equal();  // true
     * $this->compareTo($other)->less();   // false
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function compareTo(self|Unit $other, Precision $precision = Precision::Micro): Compared;

    /**
     * Returns this date-time formatted according to a given string
     * and using the global formatter or a given one.
     *
     * Supports {@see \DateTimeInterface::format()} syntax by default.
     * The time-zone characters will be omitted for `LocalDateTime`.
     *
     * @param non-empty-string $format
     *
     * @return non-empty-string
     *
     * @throws FormatError
     *
     * @see \Kronika\formatter()
     * @see \Kronika\Format\native() formatter
     */
    public function format(string $format, ?Formatter $formatter = null): string;

    /**
     * Obtains an instance of `\DateTimeImmutable` from this date-time.
     */
    public function toNative(): \DateTimeImmutable;

    /**
     * Obtains an instance of `\DateTime` from this date-time.
     */
    public function toNativeMutable(): \DateTime;

    /**
     * Obtains an instance of `\Kronika\Instant` from this date-time.
     */
    public function instant(): Instant;

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string;
}
