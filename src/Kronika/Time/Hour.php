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

namespace Kronika\Time;

use Kronika\Time;
use Kronika\Utils\Compared;
use Kronika\Utils\RefTrait;

/**
 * Represents an hour of the day.
 *
 * @psalm-type THour=int<0,23>
 * @implements TimeUnit<THour>
 */
final readonly class Hour implements TimeUnit
{
    use Trait\TimeUnit;
    use RefTrait;

    /**
     * Obtains an instance of `Hour` from a value.
     *
     * ```
     * $hour = Hour::of(12);
     * ```
     *
     * @param THour|self $value
     *
     * @throws Exception\InvalidHour
     */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : static::ref(value: $value);
    }

    /**
     * Obtains an instance of `Hour` at 0.
     *
     * ```
     * Hour::zero()->value();  // 0
     * ```
     */
    public static function zero(): self
    {
        static $zero = self::of(0);

        return $zero;
    }

    /**
     * Obtains an instance of `Hour` at the end of the day.
     *
     * ```
     * Hour::last()->value();  // 23
     * ```
     */
    public static function last(): self
    {
        static $last = self::of(23);

        return $last;
    }

    /**
     * @param THour $value
     *
     * @throws Exception\InvalidHour
     */
    private function __construct(
        private int $value,
    ) {
        self::assertValue($value);
    }

    #[\Override]
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Returns the previous hour from this one.
     *
     * ```
     * // 22
     * $this->previous();  // 21
     * ```
     * ```
     * // 00
     * $this->previous(rolling: false);  // 00
     * $this->previous(rolling: true);   // 23
     */
    public function previous(bool $rolling = false): self
    {
        if ($this->isZero()) {
            return $rolling ? self::last() : self::zero();
        }

        return self::of($this->value() - 1);
    }

    /**
     * Returns the next hour from this one.
     *
     * ```
     * // 22
     * $this->next();  // 23
     * ```
     * ```
     * // 23
     * $this->next(rolling: false);  // 23
     * $this->next(rolling: true);   // 00
     * ```
     */
    public function next(bool $rolling = false): self
    {
        if ($this->isLast()) {
            return $rolling ? self::zero() : self::last();
        }

        return self::of($this->value() + 1);
    }

    /**
     * Checks if this hour is equal to 0.
     */
    #[\Override]
    public function isZero(): bool
    {
        return $this->is(self::zero());
    }

    /**
     * Check if this hour is equal to 23.
     */
    #[\Override]
    public function isLast(): bool
    {
        return $this->is(self::last());
    }

    /**
     * Checks if this hour is equal to another one.
     *
     * ```
     * // 12
     * $this->is(Hour::of(12));  // true
     * $this->is(Hour::zero());  // false
     * $this->is(Hour::of(21));  // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this hour is not equal to another one.
     *
     * ```
     * // 12
     * $this->isNot(Hour::of(12));  // false
     * $this->isNot(Hour::zero());  // true
     * $this->isNot(Hour::of(21));  // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this hour is before another one.
     *
     * ```
     * // 12
     * $this->isBefore(Hour::of(12));  // false
     * $this->isBefore(Hour::zero());  // false
     * $this->isBefore(Hour::of(21));  // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this hour is before or equal to another one.
     *
     * ```
     * // 12
     * $this->isBeforeOrEqualTo(Hour::of(12));  // true
     * $this->isBeforeOrEqualTo(Hour::zero());  // false
     * $this->isBeforeOrEqualTo(Hour::of(21));  // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /** @deprecated {@see self::is()} */
    public function isEqualTo(self $other): bool
    {
        return $this->is($other);
    }

    /** @deprecated {@see self::isNot()} */
    public function isNotEqualTo(self $other): bool
    {
        return $this->isNot($other);
    }

    /**
     * Checks if this hour is after or equal to another one.
     *
     * ```
     * // 12
     * $this->isAfterOrEqualTo(Hour::of(12));  // true
     * $this->isAfterOrEqualTo(Hour::zero());  // true
     * $this->isAfterOrEqualTo(Hour::of(21));  // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this hour is after another one.
     *
     * ```
     * // 12
     * $this->isAfter(Hour::of(12));  // false
     * $this->isAfter(Hour::zero());  // true
     * $this->isAfter(Hour::of(21));  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this hour to another one.
     *
     * ```
     * // 12 vs 21
     * $this->compareTo($other)->less();    // true
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // false
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
        return \sprintf('%02d', $this->value());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['hour' => (string)$this];
    }

    /** @throws Exception\InvalidHour */
    private static function assertValue(int $value): void
    {
        if ($value < 0 || $value > 23) {
            throw new Exception\InvalidHour("Hour must be between 0 and 23, got [$value]");
        }
    }

    /** @internal {@see \Kronika\Time::with()} */
    #[\Override]
    public function _withinTime(Time $time): Time
    {
        return Time::of($this, $time->minute(), $time->second());
    }
}
