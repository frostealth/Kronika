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

use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Utils\Compared;
use Kronika\Utils\WeakRefsTrait;

/**
 * Represents an hour of the day.
 *
 * @psalm-type THour=int<0,23>
 * @implements TimeUnit<THour>
 */
final readonly class Hour implements TimeUnit
{
    /** @use WeakRefsTrait<static,THour> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of Hour from a value.
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
        return $value instanceof self ? $value : static::weak(value: $value);
    }

    /**
     * Obtains an instance of Hour at 0.
     *
     * ```
     * Hour::zero()->value();  // 0
     * ```
     */
    public static function zero(): self
    {
        static $instance = self::of(0);

        return $instance;
    }

    /**
     * Obtains an instance of Hour at the end of the day.
     *
     * ```
     * Hour::last()->value();  // 23
     * ```
     */
    public static function last(): self
    {
        static $instance = self::of(23);

        return $instance;
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
     * Checks if this hour is equal to 0.
     */
    #[\Override]
    public function isZero(): bool
    {
        return $this->isEqualTo(self::zero());
    }

    /**
     * Check if this hour is equal to 23.
     */
    #[\Override]
    public function isLast(): bool
    {
        return $this->isEqualTo(self::last());
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

    /**
     * Checks if this hour is equal to another one.
     *
     * ```
     * // 12
     * $this->isEqualTo(Hour::of(12));  // true
     * $this->isEqualTo(Hour::zero());  // false
     * $this->isEqualTo(Hour::of(21));  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this hour is not equal to another one.
     *
     * ```
     * // 12
     * $this->isNotEqualTo(Hour::of(12));  // false
     * $this->isNotEqualTo(Hour::zero());  // true
     * $this->isNotEqualTo(Hour::of(21));  // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
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
        return Compared::of($this->value() <=> $other->value());
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

    /** @internal {@see \Kronika\DateTime::with()} */
    #[\Override]
    public function _withinDateTime(LocalDateTime $datetime): LocalDateTime
    {
        return $datetime->with($datetime->time()->with($this));
    }
}
