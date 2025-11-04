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

namespace Kronika\Date;

use Kronika\Date;
use Kronika\DateTime;
use Kronika\Duration;
use Kronika\LocalDateTime;
use Kronika\Utils\Compared;

/**
 * Represents a month of the year.
 *
 * @psalm-type TMonth=value-of<Month>
 * @psalm-type TMonthName='January'|'February'|'March'|'April'|'May'|'June'|'July'|'August'|'September'|'October'|'November'|'December'
 * @implements DateUnit<TMonth>
 */
enum Month: int implements DateUnit
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    /**
     * Obtains an instance of Month from a number or name.
     *
     * ```
     * // January
     * $month = Month::January;
     * $month = Month::of(1);
     * $month = Month::of('january');
     * ```
     *
     * @psalm-param TMonth|TMonthName|self $value
     *
     * @throws Exception\InvalidMonth
     */
    public static function of(int|string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return \is_int($value) ? self::ofValue($value) : self::ofName($value);
    }

    /**
     * Return the number of days in this month in a given year.
     *
     * ```
     * // February
     * $this->length(Year::of(2025));  // 28
     * $this->length(Year::of(2024));  // 29 - leap year
     * ```
     */
    public function length(Year $year): int
    {
        return match ($this) {
            self::February => $year->isLeap() ? 29 : 28,
            self::April,
            self::June,
            self::September,
            self::November => 30,
            default => 31,
        };
    }

    /**
     * Checks if this month contains a given day in a given year.
     *
     * ```
     * // February
     * $this->containsDay(DayOfMonth::29, Year::of(2025));  // false
     * $this->containsDay(DayOfMonth::29, Year::of(2024));  // true - leap year
     * ```
     */
    public function containsDay(DayOfMonth $day, Year $year): bool
    {
        return $day->number() <= $this->length($year);
    }

    /**
     * Checks if the number of this month is equal to a given one.
     *
     * @deprecated
     */
    #[\Override]
    public function is(int|self $number): bool
    {
        $number = $number instanceof self ? $number->value : $number;

        return $number === $this->value;
    }

    /**
     * Returns the number of this month.
     */
    #[\Override]
    public function number(): int
    {
        return $this->value;
    }

    /**
     * Returns the name of this month.
     *
     * ```
     * Month::January->name();  // January
     * ```
     *
     * @return TMonthName
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the last day of this month in a given year.
     *
     * ```
     * // February
     * $this->lastDay(Year::of(2025));  // DayOfMonth::of(28)
     * $this->lastDay(Year::of(2024));  // DayOfMonth::of(28) - leap year
     * ```
     */
    public function lastDay(Year $year): DayOfMonth
    {
        return DayOfMonth::of($this->length($year));
    }

    /**
     * Returns the next month.
     *
     * ```
     * Month::January->next();  // February
     * ```
     */
    public function next(): self
    {
        if ($this === self::December) {
            return self::January;
        }

        return self::of($this->number() + 1);
    }

    /**
     * Returns the previous month.
     *
     * ```
     * Month::January->previous();  // December
     * ```
     */
    public function previous(): self
    {
        if ($this === self::January) {
            return self::December;
        }

        return self::of($this->number() - 1);
    }

    /**
     * Returns the duration of this month in a given year.
     *
     * ```
     * // February
     * $this->duration(Year::of(2025));  // Duration::of(days: 28)
     * $this->duration(Year::of(2024));  // Duration::of(days: 29) - leap year
     * ```
     */
    public function duration(Year $year): Duration
    {
        return Duration::of(days: $this->length($year));
    }

    /**
     * Checks if this month is before another one.
     *
     * ```
     * // July
     * $this->isBefore(Month::July);     // false
     * $this->isBefore(Month::January);  // false
     * $this->isBefore(Month::December);  // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this month is before or equal to another one.
     *
     * ```
     * // July
     * $this->isBeforeOrEqualTo(Month::July);      // true
     * $this->isBeforeOrEqualTo(Month::January);   // false
     * $this->isBeforeOrEqualTo(Month::December);  // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this month is equal to another one.
     *
     * ```
     * // July
     * $this->isEqualTo(Month::July);      // true
     * $this->isEqualTo(Month::January);   // false
     * $this->isEqualTo(Month::December);  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this month is not equal to another one.
     *
     * ```
     * // July
     * $this->isNotEqualTo(Month::July);      // false
     * $this->isNotEqualTo(Month::January);   // true
     * $this->isNotEqualTo(Month::December);  // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this month is after or equal to another one.
     *
     * ```
     * // July
     * $this->isAfterOrEqualTo(Month::July);      // true
     * $this->isAfterOrEqualTo(Month::January);   // true
     * $this->isAfterOrEqualTo(Month::December);  // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this month is after another one.
     *
     * ```
     * // July
     * $this->isAfter(Month::July);      // false
     * $this->isAfter(Month::January);   // true
     * $this->isAfter(Month::December);  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this month to another one.
     *
     * ```
     * // July vs December
     * $this->compareTo($other)->less();    // true
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // false
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::of($this->number() <=> $other->number());
    }

    /** @throws Exception\InvalidMonth */
    private static function ofName(string $name): self
    {
        try {
            return self::{\ucfirst($name)};
        } catch (\Throwable $e) {
            throw new Exception\InvalidMonth("Invalid month name [$name]", previous: $e);
        }
    }

    /** @throws Exception\InvalidMonth */
    private static function ofValue(int $value): self
    {
        try {
            return self::from($value);
        } catch (\ValueError $e) {
            throw new Exception\InvalidMonth("Invalid month value [$value]", previous: $e);
        }
    }

    /** @internal {@see Date::with()} */
    #[\Override]
    public function _withinDate(Date $date): Date
    {
        return Date::of(
            year: $date->year(),
            month: $this,
            day: $this->_adjustDay($date->day(), $date->year()),
        );
    }

    /** @internal {@see DateTime::with()} */
    #[\Override]
    public function _withinDateTime(LocalDateTime $datetime): LocalDateTime
    {
        return $datetime->with($datetime->date()->with($this));
    }

    /** @internal */
    public function _adjustDay(DayOfMonth $day, Year $year): DayOfMonth
    {
        if (! $this->containsDay($day, $year)) {
            return $this->lastDay($year);
        }

        return $day;
    }
}
