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

use DateTimeInterface as Native;
use Kronika\Date\DateUnit;
use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Utils\Compared;
use Kronika\Utils\WeakRefsTrait;

/**
 * Represents a date.
 *
 * @psalm-import-type TYear from Year
 * @psalm-import-type TMonth from Month
 * @psalm-import-type TDayOfMonth from DayOfMonth
 */
final readonly class Date implements Unit
{
    /** @use WeakRefsTrait<static,Year|TYear|Month|TMonth|DayOfMonth|TDayOfMonth> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of Date from a year, month and day of the month.
     *
     * ```
     * // 2025-12-31
     * $date = Date::of(year: 2025, month: 12, day: 31);
     * $date = Date::of(Year::of(2025), Month::December, DayOfMonth::of(31));
     * ```
     *
     * @psalm-param Year|TYear             $year
     * @psalm-param Month|TMonth           $month
     * @psalm-param DayOfMonth|TDayOfMonth $day
     */
    public static function of(Year|int $year, Month|int $month, DayOfMonth|int $day): self
    {
        return self::weak(year: Year::of($year), month: Month::of($month), day: DayOfMonth::of($day));
    }

    /**
     * Obtain an instance of Date from a date-time.
     */
    public static function ofDateTime(DateTime|Native $datetime): self
    {
        if ($datetime instanceof DateTime) {
            return $datetime->date();
        }

        [$year, $month, $day] = \sscanf($datetime->format('Y-m-d'), '%d-%d-%d');

        return self::of($year, $month, $day);
    }

    /**
     * Obtain an instance of Date from a timestamp.
     */
    public static function ofTimestamp(float|int $timestamp): self
    {
        return self::ofInstant(Instant::ofValue($timestamp));
    }

    /**
     * Obtain an instance of Date from a "Kronika\Instant".
     */
    public static function ofInstant(Instant $instant): self
    {
        /** @var \WeakMap<Instant, self> $references */
        static $references = new \WeakMap();
        if (isset($references[$instant])) {
            return $references[$instant];
        }

        ['year' => $year, 'mon' => $month, 'mday' => $day] = \getdate($instant->second());

        return $references[$instant] = self::of($year, $month, $day);
    }

    /**
     * Obtain an instance of Date from a format.
     *
     * @param non-empty-string $format
     * @param non-empty-string $date
     */
    public static function ofFormat(string $format, string $date): self
    {
        return self::ofDateTime(\DateTimeImmutable::createFromFormat(self::quote($format), $date));
    }

    private function __construct(
        private Year       $year,
        private Month      $month,
        private DayOfMonth $day,
    ){
        \assert($month->containsDay($day, $year));
    }

    /**
     * Returns an instance of Year from this date.
     */
    public function year(): Year
    {
        return $this->year;
    }

    /**
     * Returns an instance of Month from this date.
     */
    public function month(): Month
    {
        return $this->month;
    }

    /**
     * Returns an instance of Day from this date.
     */
    public function day(): DayOfMonth
    {
        return $this->day;
    }

    /**
     * Returns an instance of DayOfWeek from this date.
     */
    public function dayOfWeek(): DayOfWeek
    {
        return DayOfWeek::of(\getdate($this->instant()->second())['wday']);
    }

    /**
     * Returns an instance of Date with a given date unit.
     *
     * If the day of the month of the resulting date is greater than
     * the length of the month, then the last day of the month will be set.
     *
     * ```
     * // 2025-11-29
     * $this->with(Year::of(1990));         // 1990-11-29
     * $this->with($this->year()->next());  // 2026-11-29
     * $this->with(Month::January);         // 2025-01-29
     * $this->with(Month::February);        // 2025-02-28
     * $this->with(DayOfMonth::of(15));     // 2025-11-15
     * $this->with(DayOfMonth::of(31));     // 2025-11-30
     * $this->with(DayOfWeek::Monday);      // 2025-11-24
     * ```
     */
    public function with(DateUnit $unit): self
    {
        return $unit->_withinDate($this);
    }

    /**
     * Returns an instance of LocalDateTime with this date and a given time.
     *
     * ```
     * // 2025-12-31
     * $this->at(Time::of(12, 15, 30));  // 2025-12-31 12:15:30
     * ```
     */
    public function at(Time $time): LocalDateTime
    {
        return LocalDateTime::of(date: $this, time: $time);
    }

    /**
     * Returns an instance of LocalDateTime with this date and midnight time.
     *
     * ```
     * // 2025-12-31
     * $this->atMidnight();  // 2025-12-31 00:00:00.000000
     * ```
     */
    public function atMidnight(): LocalDateTime
    {
        return $this->at(Time::midnight());
    }

    /**
     * Returns an instance of LocalDateTime with this date and midday/noon time.
     *
     * ```
     * // 2025-12-31
     * $this->atMidday();  // 2025-12-31 12:00:00.000000
     * ```
     */
    public function atMidday(): LocalDateTime
    {
        return $this->at(Time::midday());
    }

    /**
     * Returns an instance of LocalDateTime with this date and time of the end of the day.
     *
     * ```
     * // 2025-12-31
     * $this->atEndOfDay();  // 2025-12-31 23:59:59.999999
     * ```
     */
    public function atEndOfDay(): LocalDateTime
    {
        return $this->at(Time::endOfDay());
    }

    /**
     * Adds an amount of days, hours, minutes and seconds to this date.
     *
     * ```
     * // 2025-12-31 + 2 days
     * $this->add(Duration::of(days: 2));  // 2026-01-12
     *
     * // 2025-12-31 + 24 hours
     * $this->add(Duration::of(hours: 24));  // 2026-01-01
     * ```
     */
    public function add(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            return self::ofDateTime($this->at(Time::midnight())->add($duration));
        }

        return self::ofInstant($this->instant()->add($duration->roundToDays()));
    }


    /**
     * Subtracts an amount of days, hours, minutes and seconds to this date.
     *
     * ```
     * // 2025-12-31 - 2 days
     * $this->sub(Duration::of(days: 2));  // 2025-12-29
     *
     * // 2025-12-31 - 24 hours
     * $this->sub(Duration::of(hours: 24));  // 2025-12-30
     * ```
     */
    public function sub(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            return self::ofDateTime($this->at(Time::endOfDay())->sub($duration));
        }

        return self::ofInstant($this->instant()->sub($duration->roundToDays()));
    }

    /**
     * Returns a duration from this date or its unit to another one.
     *
     * ```
     * // 2025-12-31 vs 2026-01-02
     * $duration = $this->until($other);
     * $duration->days();   // 2
     *
     * // 2026-01-02 vs 2025-12-31
     * $duration = $this->until($other);
     * $duration->days();   // 0
     *
     * // 2026-01-02 vs Month::of(2)
     * $duration = $this->until($other);
     * $duration->days();   // 31
     * ```
     */
    public function until(self|DateUnit $end): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant());
    }

    /**
     * Returns a duration between this date or its unit and another one.
     *
     * ```
     * // 2025-12-31 vs 2026-01-02
     * $duration = $this->difference($other);
     * $duration->days();   // 2
     *
     * // 2026-01-02 vs 2025-12-31
     * $duration = $this->difference($other);
     * $duration->days();   // 2
     * ```
     */
    public function difference(self|DateUnit $other): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant());
    }

    /**
     * Returns an instance of Date with the first day of the month.
     *
     * ```
     * // 2025-12-31
     * $this->toStartOfMonth();  // 2025-12-01
     * ```
     */
    public function toStartOfMonth(): self
    {
        return $this->with(DayOfMonth::of(1));
    }

    /**
     * Returns an instance of Date with the last day of the month.
     *
     * ```
     * // 2025-02-01
     * $this->toEndOfMonth();  // 2025-02-28
     *
     * // 2024-02-01 – leap year
     * $this->toEndOfMonth();  // 2025-02-29
     * ```
     */
    public function toEndOfMonth(): self
    {
        return $this->with($this->month()->lastDay($this->year()));
    }

    /**
     * Checks if the day of the month of this date is the first in the month.
     *
     * ```
     * // 2025-12-01
     * $this->isStartOfMonth();  // true
     *
     * // 2025-12-31
     * $this->isStartOfMonth();  // false
     * ```
     */
    public function isStartOfMonth(): bool
    {
        return $this->day()->isEqualTo(DayOfMonth::of(1));
    }

    /**
     * Checks if the day of the month of this date is the last in the month.
     *
     * ```
     * // 2025-12-31
     * $this->isEndOfMonth();  // true
     *
     * // 2025-12-01
     * $this->isEndOfMonth();  // false
     * ```
     */
    public function isEndOfMonth(): bool
    {
        return $this->day()->isEqualTo($this->month()->lastDay($this->year()));
    }

    /**
     * Checks if this date or its unit is before another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->isBefore($other);  // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->isBefore($other);  // false
     *
     * // 2025-12-30 vs Year::of(2026)
     * $this->isBefore($other);  // true
     * ```
     */
    public function isBefore(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this date or its unit is before or equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 2025-12-31 vs 2025-12-30
     * $this->isBeforeOrEqualTo($other);  // false
     *
     * // 2025-12-31 vs DayOfMonth::of(30)
     * $this->isBeforeOrEqualTo($other);  // false
     * ```
     */
    public function isBeforeOrEqualTo(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this date or its unit is equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isEqualTo($other);  // false
     *
     * // 2025-12-30 vs Month::December
     * $this->isEqualTo($other);  // true
     * ```
     */
    public function isEqualTo(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this date or its unit is not equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->isNotEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->isNotEqualTo($other);  // false
     *
     * // 2025-12-30 vs Month::December
     * $this->isNotEqualTo($other);  // false
     * ```
     */
    public function isNotEqualTo(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this date or its unit is after or equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isAfterOrEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isAfterOrEqualTo($other);  // false
     *
     * // 2025-12-30 vs Year::of(2025)
     * $this->isAfterOrEqualTo($other);  // true
     * ```
     */
    public function isAfterOrEqualTo(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this date or its unit is after another one.
     *
     * ```
     * // 2025-12-31 vs 2025-12-30
     * $this->isAfter($other);  // true
     *
     * // 2025-12-31 vs 2025-12-31
     * $this->isAfter($other);  // false
     *
     * // 2025-12-31 vs Year::of(2025)
     * $this->isAfter($other);  // false
     * ```
     */
    public function isAfter(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this date or its unit to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->compareTo($other)->equal();        // true
     * $this->compareTo($other)->less();         // false
     * $this->compareTo($other)->lessOrEqual();  // true
     *
     * // 2025-12-30 vs DayOfMonth::of(31)
     * $this->compareTo($other)->equal();        // false
     * $this->compareTo($other)->less();         // true
     * ```
     */
    public function compareTo(self|DateUnit $other): Compared
    {
        return $this->instant()->compareTo($this->normalize($other)->instant());
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return $this->at(Time::midnight())->format(self::quote($format));
    }

    /**
     * Returns an instance of Instant with this date.
     */
    public function instant(): Instant
    {
        return Instant::of(\strtotime((string)$this));
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%04d-%02d-%02d', $this->year()->number(), $this->month()->number(), $this->day()->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['date' => (string)$this];
    }

    /** @internal {@see DateTime::with()} */
    #[\Override]
    public function _withinDateTime(LocalDateTime $datetime): LocalDateTime
    {
        return $this->at($datetime->time());
    }

    private static function quote(string $format): string
    {
        return \preg_replace('/(?<!\\\\)([^DdjlNSWwzFMmntLoXxYy:\\\\\s\d-])/', '\\\\$1', $format);
    }

    private function normalize(self|DateUnit $date): self
    {
        return $date instanceof DateUnit ? $this->with($date) : $date;
    }
}
