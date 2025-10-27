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
use Kronika\Utils\Compared;

/**
 * Represents a day of month.
 *
 * @psalm-type TDayOfMonth=int<1,31>
 * @implements DateUnit<TDayOfMonth>
 */
final readonly class DayOfMonth implements DateUnit
{
    /** @use DateUnitTrait<TDayOfMonth> */
    use DateUnitTrait;

    /**
     * Obtains an instance of DayOfMonth from a given number.
     *
     * ```
     * $day = DayOfMonth::of(20);
     * ```
     *
     * @param TDayOfMonth|DayOfMonth $value
     */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::weak(value: $value);
    }

    /**
     * Checks if this day of month is before another one.
     *
     * ```
     * // 20
     * $this->isBefore(DayOfMonth::of(20));  // false
     * $this->isBefore(DayOfMonth::of(1));   // false
     * $this->isBefore(DayOfMonth::of(31));  // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this day of month is before or equal to another one.
     *
     * ```
     * // 20
     * $this->isBeforeOrEqualTo(DayOfMonth::of(20));  // true
     * $this->isBeforeOrEqualTo(DayOfMonth::of(1));   // false
     * $this->isBeforeOrEqualTo(DayOfMonth::of(31));  // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this day of month is equal to another one.
     *
     * ```
     * // 20
     * $this->isEqualTo(DayOfMonth::of(20));  // true
     * $this->isEqualTo(DayOfMonth::of(1));   // false
     * $this->isEqualTo(DayOfMonth::of(31));  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this day of month is not equal to another one.
     *
     * ```
     * // 20
     * $this->isNotEqualTo(DayOfMonth::of(20));  // false
     * $this->isNotEqualTo(DayOfMonth::of(1));   // true
     * $this->isNotEqualTo(DayOfMonth::of(31));  // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return ! $this->isEqualTo($other);
    }

    /**
     * Checks if this day of month is after or equal to another one.
     *
     * ```
     * // 20
     * $this->isAfterOrEqualTo(DayOfMonth::of(20));  // true
     * $this->isAfterOrEqualTo(DayOfMonth::of(1));   // true
     * $this->isAfterOrEqualTo(DayOfMonth::of(31));  // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this day of month is after another one.
     *
     * ```
     * // 20
     * $this->isAfter(DayOfMonth::of(20));  // false
     * $this->isAfter(DayOfMonth::of(1));   // true
     * $this->isAfter(DayOfMonth::of(31));  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this day of month to another one.
     *
     * ```
     * // 20 vs 31
     * $this->compareTo($other)->less();    // true
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // false
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::compare($this->number(), $other->number());
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%02d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['dayOfMonth' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinDate(Date $date): Date
    {
        return Date::of(
            year: $date->year(),
            month: $date->month(),
            day: $date->month()->adjustDay($this, $date->year()),
        );
    }

    #[\Override]
    protected static function minValue(): int
    {
        return 1;
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 31;
    }
}
