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
 * Represents a day of year.
 *
 * @psalm-type TDayOfYear=int<1,366>
 * @implements DateUnit<TDayOfYear>
 */
final readonly class DayOfYear implements DateUnit
{
    /** @use RefTrait<static> */
    use RefTrait;
    use Trait\DateUnit;

    /**
     * Obtains an instance of `DayOfYear` from a given number.
     *
     * ```
     * // February 1
     * $dayOfYear = DayOfYear::of(32);
     * ```
     *
     * @param TDayOfYear|self $value
     *
     * @throws Exception\InvalidDayOfYear
     */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::ref(number: $value);
    }

    /**
     * Obtains an instance of `DayOfYear` with the first day of year.
     *
     * ```
     * // January 1
     * DayOfYear::first()->number();  // 1
     * ```
     *
     * @see self::number()
     */
    public static function first(): self
    {
        static $first = self::of(1);

        return $first;
    }

    /**
     * Obtains an instance of `DayOfYear` with the last day of year.
     *
     * By default, it is 366 (leap year).
     * Give a year to retrieve the last day of that year.
     *
     * ```
     * DayOfYear::last()->number();  // 366
     * DayOfYear::last(Year::of(2025))->number();  // 365
     * DayOfYear::last(Year::of(2024))->number();  // 366
     * ```
     *
     * @see self::number()
     */
    public static function last(?Year $ofYear = null): self
    {
        static $leap = self::of(366);
        static $nonLeap = self::of(365);

        return $ofYear?->isLeap() !== false ? $leap : $nonLeap;
    }

    /**
     * @param TDayOfYear $number
     *
     * @throws Exception\InvalidDayOfYear
     */
    private function __construct(
        private int $number,
    ) {
        self::assertNumber($number);
    }

    #[\Override]
    public function number(): int
    {
        return $this->number;
    }

    /**
     * Returns the previous day of year.
     *
     * ```
     * // 300
     * $this->previous();  // 299
     * ```
     * ```
     * // 1
     * $this->previous();  // 366
     * $this->previous(rolling: false);  // 1
     * $this->previous(Year::of(2026));  // 365
     * ```
     */
    public function previous(?Year $ofYear = null, bool $rolling = false): self
    {
        $number = $this->adjust($ofYear)->number() - 1;
        if ($number < 1) {
            return $rolling ? self::last($ofYear?->previous()) : self::first();
        }

        return self::of($number);
    }

    /**
     * Returns the next day of year.
     *
     * ```
     * // 300
     * $this->next();  // 301
     * ```
     * ```
     * // 365
     * $this->next();  // 366
     * $this->next(Year::of(2025));  // 365
     * $this->next(Year::of(2025), rolling: true);  // 1
     * ```
     */
    public function next(?Year $ofYear = null, bool $rolling = false): self
    {
        $number = $this->number() + 1;
        if ($number > self::last($ofYear)->number()) {
            return $rolling ? self::first() : self::last($ofYear);
        }

        return self::of($number);
    }

    /**
     * Checks if this day of year is before another one.
     *
     * ```
     * // 32
     * $this->isBefore(DayOfYear::of(32));  // false
     * $this->isBefore(DayOfYear::of(1));   // false
     * $this->isBefore(DayOfYear::of(36));  // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this day of year is before or equal to another one.
     *
     * ```
     * // 32
     * $this->isBeforeOrEqualTo(DayOfYear::of(32));  // true
     * $this->isBeforeOrEqualTo(DayOfYear::of(1));   // false
     * $this->isBeforeOrEqualTo(DayOfYear::of(36));  // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this day of year is equal to another one.
     *
     * ```
     * // 32
     * $this->isEqualTo(DayOfYear::of(32));  // true
     * $this->isEqualTo(DayOfYear::of(1));   // false
     * $this->isEqualTo(DayOfYear::of(36));  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this day of year is not equal to another one.
     *
     * ```
     * // 32
     * $this->isNotEqualTo(DayOfYear::of(32));  // false
     * $this->isNotEqualTo(DayOfYear::of(1));   // true
     * $this->isNotEqualTo(DayOfYear::of(36));  // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this day of year is after or equal to another one.
     *
     * ```
     * // 32
     * $this->isAfterOrEqualTo(DayOfYear::of(32));  // true
     * $this->isAfterOrEqualTo(DayOfYear::of(1));   // true
     * $this->isAfterOrEqualTo(DayOfYear::of(36));  // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }


    /**
     * Checks if this day of year is after another one.
     *
     * ```
     * // 32
     * $this->isAfter(DayOfYear::of(32));  // false
     * $this->isAfter(DayOfYear::of(1));   // true
     * $this->isAfter(DayOfYear::of(36));  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this day of year to another one.
     *
     * ```
     * // 32 vs 36
     * $this->compareTo($other)->less();    // true
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // false
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::of($this->number() <=> $other->number());
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return (string)$this->number();
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['dayOfYear' => $this->number()];
    }

    /** @throws Exception\InvalidDayOfYear */
    private static function assertNumber(int $number): void
    {
        if ($number < 1 || $number > 366) {
            throw new Exception\InvalidDayOfYear("Day of year must be between 1 and 366, got [$number]");
        }
    }

    private function difference(self $other): Duration
    {
        return Duration::of(days: \abs($this->number() - $other->number()));
    }

    private function isFirst(): bool
    {
        return $this->isEqualTo(self::first());
    }

    private function isLast(?Year $ofYear): bool
    {
        return $this->adjust($ofYear)->isEqualTo(self::last($ofYear));
    }

    private function adjust(?Year $year): self
    {
        return $this->number() <= 365 ? $this : self::last($year);
    }

    /** @internal {@see \Kronika\Date::with()} */
    #[\Override]
    public function _withinDate(Date $date, bool $rolling): Date
    {
        if ($this->isFirst()) {
            return $date->toStartOfYear();
        }
        if (! $rolling && $this->isLast($date->year())) {
            return $date->toEndOfYear();
        }
        if ($this->isBefore($date->dayOfYear())) {
            return $date->sub($this->adjust($date->year())->difference($date->dayOfYear()));
        }

        return $date->add($this->difference($date->dayOfYear()));
    }
}
