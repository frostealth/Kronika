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
    /** @use WeakRefsTrait<self,Year|TYear|Month|TMonth|DayOfMonth|TDayOfMonth> */
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
    public static function ofDateTime(DateTime|\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->date();
        }

        [$year, $month, $day] = \sscanf($dateTime->format('Y-m-d'), '%d-%d-%d');

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
        return self::ofDateTime(\DateTimeImmutable::createFromFormat($format, $date));
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
        return $unit->withinDate($this);
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
            return self::ofDateTime(LocalDateTime::midnightOf($this)->add($duration));
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
            return self::ofDateTime(LocalDateTime::endOfDayOf($this)->sub($duration));
        }

        return self::ofInstant($this->instant()->sub($duration->roundToDays()));
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return $this->at(Time::midnight())->format(
            // @todo: the escaping doesn't work
            \preg_replace('/([^DdjlNSWwzFMmntLoXxYy])/', '\\\\$1', $format),
        );
    }

    /**
     * Returns an instance of Duration from this date to another.
     *
     * ```
     * // 2025-12-31 vs 2026-01-02
     * $duration = $this->until($other);
     * $duration->days();   // 2
     *
     * // 2026-01-02 vs 2025-12-31
     * $duration = $this->until($other);
     * $duration->days();   // 0
     * ```
     */
    public function until(self $end): Duration
    {
        return $this->instant()->until($end->instant());
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
     * Checks if this date is before another.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->isBefore($other);  // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->isBefore($other);  // false
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this date is before or equal to another.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isBeforeOrEqual($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isBeforeOrEqual($other);  // true
     *
     * // 2025-12-31 vs 2025-12-30
     * $this->isBeforeOrEqual($other);  // false
     * ```
     */
    public function isBeforeOrEqual(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this date is equal to another.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isEqualTo($other);  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this date is not equal to another.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->isNotEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->isNotEqualTo($other);  // false
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return ! $this->isEqualTo($other);
    }

    /**
     * Checks if this date is after or equal to another.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isAfterOrEqual($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isAfterOrEqual($other);  // false
     * ```
     */
    public function isAfterOrEqual(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this date is after another.
     *
     * ```
     * // 2025-12-31 vs 2025-12-30
     * $this->isAfter($other);  // true
     *
     * // 2025-12-31 vs 2025-12-31
     * $this->isAfter($other);  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this date with another.
     *
     * ```
     * // 12025-12-30 vs 2025-12-31
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     *
     * // 12025-12-30 vs 2025-12-30
     * $this->compareTo($other)->equal();        // true
     * $this->compareTo($other)->less();         // false
     * $this->compareTo($other)->lessOrEqual();  // true
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return $this->instant()->compareTo($other->instant());
    }

    /**
     * Returns an instance of Instant with this date.
     */
    public function instant(): Instant
    {
        return Instant::of(\strtotime((string)$this));
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%04d-%02d-%02d', $this->year()->number(), $this->month()->number(), $this->day()->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['date' => (string)$this];
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $this->at($dateTime->time());
    }
}
