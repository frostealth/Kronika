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

use Kronika\DateTime;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Utils\Compared;
use Kronika\Utils\WeakRefsTrait;
use function Kronika\Utils\Math\double;
use function Kronika\Utils\Math\double_split;

/**
 * Represents a second with a microsecond of the minute.
 *
 * @psalm-type TSecond=int<0,59>
 * @psalm-type TMicrosecond=int<0,999999>
 * @implements TimeUnit<TSecond>
 */
final readonly class Second implements TimeUnit
{
    /** @use WeakRefsTrait<static,TSecond|TMicrosecond> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of Second from given values (second and microsecond).
     *
     * ```
     * // 45 second and 0 microsecond
     * $second = Second::of(45);
     *
     * // 45 second and 999999 microsecond
     * $second = Second::of(45, 999999);
     * ```
     *
     * @param TSecond|self $second
     * @param TMicrosecond|null $micro
     */
    public static function of(int|self $second, ?int $micro = null): self
    {
        if ($second instanceof self) {
            return $micro === null ? $second : self::of($second->second(), $micro);
        }

        return self::weak(second: $second, microsecond: $micro ?? 0);
    }

    /**
     * Obtains an instance of Second at 0.000000.
     *
     * ```
     * $second = Second::zero();
     * $second->value();        // 0.000000
     * $second->second();       // 0
     * $second->microsecond();  // 0
     * ```
     */
    public static function zero(): self
    {
        static $instance = self::of(second: 0, micro: 0);

        return $instance;
    }

    /**
     * Obtains an instance of Second at the end of the minute.
     *
     * ```
     * $second = Second::lase();
     * $second->value();        // 59.999999
     * $second->second();       // 59
     * $second->microsecond();  // 999999
     * ```
     */
    public static function last(): self
    {
        static $instance = self::of(second: 59, micro: 999_999);

        return $instance;
    }

    /**
     * @param TSecond $second
     * @param TMicrosecond $microsecond
     */
    private function __construct(
        private int $second,
        private int $microsecond,
    ) {
        \assert($second >= 0 && $second < 60);
        \assert($microsecond >= 0 && $microsecond < 1_000_000);
    }

    /**
     * Returns the integer part of this second.
     *
     * @return TSecond
     */
    public function second(): int
    {
        return $this->second;
    }

    /**
     * Returns the microsecond of this second.
     *
     * @return TMicrosecond
     */
    public function microsecond(): int
    {
        return $this->microsecond;
    }

    /**
     * Returns the value of this second.
     */
    public function value(): float
    {
        return double([$this->second, $this->microsecond], precision: 6);
    }

    /**
     * Resets a microsecond to 0.
     *
     * ```
     * // 45.004545
     * $this->resetMicro();  // 45.000000
     * ```
     */
    public function resetMicro(): self
    {
        return self::of($this->second(), micro: 0);
    }

    /**
     * Checks if this second is equal to 0.000000.
     */
    #[\Override]
    public function isZero(): bool
    {
        return $this->isEqualTo(self::zero());
    }

    /**
     * Check if this second is equal to 59.999999.
     */
    #[\Override]
    public function isLast(): bool
    {
        return $this->isEqualTo(self::last());
    }

    /**
     * Checks if this second's value equal to a given one.
     *
     * @param TSecond|numeric $value
     */
    #[\Override]
    public function is(int|string|float $value): bool
    {
        [$second, $micro] = double_split($value, precision: 6);

        return $this->second === $second && $this->microsecond === $micro;
    }

    /**
     * Checks if this second is before another one.
     *
     * ```
     * // 45.004545
     * $this->isBefore(Second::of(45, 4545));  // false
     * $this->isBefore(Second::zero());        // false
     * $this->isBefore(Second::of(50));        // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this second is before or equal to another one.
     *
     * ```
     * // 45.004545
     * $this->isBeforeOrEqualTo(Second::of(45, 4545));  // true
     * $this->isBeforeOrEqualTo(Second::zero());        // false
     * $this->isBeforeOrEqualTo(Second::of(50));        // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this second is equal to another one.
     *
     * ```
     * // 45.004545
     * $this->isEqualTo(Second::of(45, 4545));  // true
     * $this->isEqualTo(Second::zero());        // false
     * $this->isEqualTo(Second::of(50));        // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this second is not equal to another one.
     *
     * ```
     * // 45.004545
     * $this->isNotEqualTo(Second::of(45, 4545));  // false
     * $this->isNotEqualTo(Second::zero());        // true
     * $this->isNotEqualTo(Second::of(50));        // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this second is after or equal to another one.
     *
     * ```
     * // 45.004545
     * $this->isAfterOrEqualTo(Second::of(45, 4545));  // true
     * $this->isAfterOrEqualTo(Second::zero());        // true
     * $this->isAfterOrEqualTo(Second::of(50));        // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this second is after another one.
     *
     * ```
     * // 45.004545
     * $this->isAfter(Second::of(45, 4545));  // false
     * $this->isAfter(Second::zero());        // true
     * $this->isAfter(Second::of(50));        // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this second to another one.
     *
     * ```
     * // 45.004545 vs 45.000000
     * $this->compareTo($other)->less();    // false
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // true
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::of((string)$this <=> (string)$other);
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%02d.%06d', $this->second, $this->microsecond);
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['second' => (string)$this];
    }

    /** @internal {@see Time::with()} */
    #[\Override]
    public function _withinTime(Time $time): Time
    {
        return Time::of($time->hour(), $time->minute(), $this);
    }

    /** @internal {@see DateTime::with()} */
    #[\Override]
    public function _withinDateTime(LocalDateTime $datetime): LocalDateTime
    {
        return $datetime->with($datetime->time()->with($this));
    }
}
