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
use Kronika\Duration;
use Kronika\Utils\Compared;
use Kronika\Utils\RefTrait;

/**
 * Represents a day of month.
 *
 * @psalm-type TDayOfMonth=int<1,31>
 * @implements DateUnit<TDayOfMonth>
 */
final readonly class DayOfMonth implements DateUnit
{
    use Trait\DateUnit;
    use RefTrait;

    /**
     * Obtains an instance of `DayOfMonth` from a given number.
     *
     * ```
     * $day = DayOfMonth::of(20);
     * ```
     *
     * @throws Exception\InvalidDayOfMonth
     */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::ref(number: $value);
    }

    /**
     * Obtains an instance of `DayOfMonth` with the first day of month.
     *
     * ```
     * DayOfMonth::first()->number();  // 1
     * ```
     */
    public static function first(): self
    {
        static $first = DayOfMonth::of(1);

        return $first;
    }

    /**
     * @param TDayOfMonth $number
     *
     * @throws Exception\InvalidDayOfMonth
     */
    private function __construct(
        private int $number,
    ) {
        self::assertNumber($number);
    }

    /**
     * Returns the number of this day of month.
     *
     * @return TDayOfMonth
     */
    #[\Override]
    public function number(): int
    {
        return $this->number;
    }

    /**
     * Checks if this day of month is equal to another one.
     *
     * ```
     * // 20
     * $this->is(DayOfMonth::of(20));  // true
     * $this->is(DayOfMonth::of(1));   // false
     * $this->is(DayOfMonth::of(31));  // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this day of month is not equal to another one.
     *
     * ```
     * // 20
     * $this->isNot(DayOfMonth::of(20));  // false
     * $this->isNot(DayOfMonth::of(1));   // true
     * $this->isNot(DayOfMonth::of(31));  // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
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
        return Compared::of($this <=> $other);
    }

    private function difference(self $other): Duration
    {
        return Duration::of(days: \abs($this->number() - $other->number()));
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%02d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['dayOfMonth' => (string)$this];
    }

    /**
     * @throws Exception\InvalidDayOfMonth
     *
     * @psalm-assert TDayOfMonth $number
     */
    private static function assertNumber(int $number): void
    {
        if ($number < 1 || $number > 31) {
            throw new Exception\InvalidDayOfMonth("Day of month must be between 1 and 12, got [$number]");
        }
    }

    /** @internal {@see \Kronika\Date::with()} */
    #[\Override]
    public function _withinDate(Date $date, bool $rolling): Date
    {
        if ($date->month()->containsDay($this, $date->year())) {
            return Date::of(year: $date->year(), month: $date->month(), day: $this);
        }

        return $rolling ? $date->add($this->difference($date->day())) : Date::of(
            year: $year = $date->year(),
            month: $month = $date->month(),
            day: $month->lastDay($year),
        );
    }
}
