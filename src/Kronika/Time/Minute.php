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
use Kronika\Utils\WeakRefsTrait;

/**
 * Represents a minute of the hour.
 *
 * @psalm-type TMinute=int<0,59>
 * @implements TimeUnit<TMinute>
 */
final readonly class Minute implements TimeUnit
{
    /** @use WeakRefsTrait<static,TMinute> */
    use WeakRefsTrait;
    use Trait\TimeUnit;

    /**
     * Obtains an instance of `Minute` from a value.
     *
     * ```
     * $minute = Minute::of(30);
     * ```
     *
     * @param TMinute|self $value
     *
     * @throws Exception\InvalidMinute
     */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : static::weak(value: $value);
    }

    /**
     * Obtains an instance of `Minute` at 0.
     *
     * ```
     * Minute::zero()->value();  // 0
     * ```
     */
    public static function zero(): self
    {
        static $instance = self::of(0);

        return $instance;
    }

    /**
     * Obtains an instance of `Minute` at the end of the hour.
     *
     * ```
     * Minute::last()->value();  // 23
     * ```
     */
    public static function last(): self
    {
        static $instance = self::of(59);

        return $instance;
    }

    /**
     * @param TMinute $value
     *
     * @throws Exception\InvalidMinute
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
     * Checks if this minute is equal to 0.
     */
    #[\Override]
    public function isZero(): bool
    {
        return $this->isEqualTo(self::zero());
    }

    /**
     * Check if this minute is equal to 59.
     */
    #[\Override]
    public function isLast(): bool
    {
        return $this->isEqualTo(self::last());
    }

    /**
     * Checks if this minute is before another one.
     *
     * ```
     * // 30
     * $this->isBefore(Minute::of(30));  // false
     * $this->isBefore(Minute::zero());  // false
     * $this->isBefore(Minute::of(50));  // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this minute is before or equal to another one.
     *
     * ```
     * // 30
     * $this->isBeforeOrEqualTo(Minute::of(30));  // true
     * $this->isBeforeOrEqualTo(Minute::zero());  // false
     * $this->isBeforeOrEqualTo(Minute::of(50));  // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this minute is equal to another one.
     *
     * ```
     * // 30
     * $this->isEqualTo(Minute::of(30));   // true
     * $this->isEqualTo(Minute::zero());  // false
     * $this->isEqualTo(Minute::of(50));  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this minute is not equal to another one.
     *
     * ```
     * // 30
     * $this->isNotEqualTo(Minute::of(30);   // false
     * $this->isNotEqualTo(Minute::zero());  // true
     * $this->isNotEqualTo(Minute::of(50));  // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this minute is after or equal to another one.
     *
     * ```
     * // 30
     * $this->isAfterOrEqualTo(Minute::of(30));  // true
     * $this->isAfterOrEqualTo(Minute::zero());  // true
     * $this->isAfterOrEqualTo(Minute::of(50));  // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this minute is after another one.
     *
     * ```
     * // 30
     * $this->isAfter(Minute::of(30));  // false
     * $this->isAfter(Minute::zero());  // true
     * $this->isAfter(Minute::of(50));  // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this minute to another one.
     *
     * ```
     * // 30 vs 0
     * $this->compareTo($other)->less();    // false
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // true
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
        return ['minute' => (string)$this];
    }

    /** @throws Exception\InvalidMinute */
    private static function assertValue(int $value): void
    {
        if ($value < 0 || $value > 59) {
            throw new Exception\InvalidMinute("Minute must be between 0 and 59, got [$value]");
        }
    }

    /** @internal {@see \Kronika\Time::with()} */
    #[\Override]
    public function _withinTime(Time $time): Time
    {
        return Time::of($time->hour(), $this, $time->second());
    }
}
