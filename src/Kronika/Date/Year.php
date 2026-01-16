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
 * Represents a year.
 *
 * @psalm-type TYear=int<-9999,9999>
 * @implements DateUnit<TYear>
 */
final readonly class Year implements DateUnit
{
    use Trait\DateUnit;
    use RefTrait;

    /**
     * Obtains an instance of `Year` from a number.
     *
     * ```
     * $year = Year::of(2025);
     * $year = Year::of(1980);
     * ```
     *
     * @param TYear|self $value
     *
     * @throws Exception\InvalidYear
     */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::ref(number: $value);
    }

    /**
     * @param TYear $number
     *
     * @throws Exception\InvalidYear
     */
    private function __construct(
        private int $number,
    ) {
        self::assertValue($number);
    }

    /**
     * Returns the number of this year.
     *
     * @return TYear
     */
    #[\Override]
    public function number(): int
    {
        return $this->number;
    }

    /**
     * Returns the next year.
     *
     * ```
     * // 2025
     * $this->next();  // 2026
     * ```
     */
    public function next(): self
    {
        return self::of(\min($this->number() + 1, 9999));
    }

    /**
     * Returns the previous year.
     *
     * ```
     * // 2025
     * $this->previous();  // 2024
     * ```
     */
    public function previous(): self
    {
        return self::of(\max($this->number() - 1, -9999));
    }

    /**
     * Returns the count of days in this year.
     *
     * ```
     * // 2025
     * $this->length();  // 365
     *
     * // 2024 - leap year
     * $this->length();  // 366
     * ```
     *
     * @return int<365,366>
     */
    public function length(): int
    {
        return $this->isLeap() ? 366 : 365;
    }

    /**
     * Returns the duration of this year.
     *
     * ```
     * // 2025
     * $this->duration();  // Duration::of(days: 365)
     *
     * // 2024 - leap year
     * $this->duration();  // Duration::of(days: 366)
     * ```
     */
    public function duration(): Duration
    {
        return Duration::of(days: $this->length());
    }

    /**
     * Checks if this year is leap.
     *
     * ```
     * Year::of(2025)->isLeap();  // false
     * Year::of(2024)->isLeap();  // true
     * ```
     */
    public function isLeap(): bool
    {
        if ($this->number() % 4 !== 0) {
            return false;
        }
        if ($this->number() % 100 !== 0) {
            return true;
        }

        return $this->number() % 400 === 0;
    }

    /**
     * Checks if this year is equal to another one.
     *
     * ```
     * // 2025
     * $this->is(Year::of(2025));  // true
     * $this->is(Year::of(2000));  // false
     * $this->is(Year::of(2050));  // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this year is not equal to another one.
     *
     * ```
     * // 2025
     * $this->isNot(Year::of(2025));  // false
     * $this->isNot(Year::of(2000));  // true
     * $this->isNot(Year::of(2050));  // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this year is before another one.
     *
     * ```
     * // 2025
     * $this->isBefore(Year::of(2025));  // false
     * $this->isBefore(Year::of(2000));  // false
     * $this->isBefore(Year::of(2050));  // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this year is before or equal to another one.
     *
     * ```
     * // 2025
     * $this->isBeforeOrEqualTo(Year::of(2025));  // true
     * $this->isBeforeOrEqualTo(Year::of(2000));  // false
     * $this->isBeforeOrEqualTo(Year::of(2050));  // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this year is after or equal to another one.
     *
     * ```
     * // 2025
     * $this->isAfterOrEqualTo(Year::of(2025));  // true
     * $this->isAfterOrEqualTo(Year::of(2000));  // true
     * $this->isAfterOrEqualTo(Year::of(2050));  // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this year is after another one.
     *
     * ```
     * // 2025
     * $this->isAfter(Year::of(2025));  // false
     * $this->isAfter(Year::of(2000));  // true
     * $this->isAfter(Year::of(2050));  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this year to another one.
     *
     * ```
     * // 2025 vs 2000
     * $this->compareTo($other)->less();    // false
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // true
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::of($this <=> $other);
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%04d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['year' => (string)$this];
    }

    /** @throws Exception\InvalidYear */
    private static function assertValue(int $value): void
    {
        if ($value < -9999 || $value > 9999) {
            throw new Exception\InvalidYear("Year must be between -9999 and 9999, got [$value]");
        }
    }

    /** @internal {@see \Kronika\Date::with()} */
    #[\Override]
    public function _withinDate(Date $date, bool $rolling): Date
    {
        return Date::of($this, $date->month(), DayOfMonth::first())->with($date->day(), $rolling);
    }
}
