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

/**
 * Represents a date-time.
 */
interface DateTime
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
}
