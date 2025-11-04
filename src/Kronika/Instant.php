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

use Kronika\Utils\Compared;
use Kronika\Utils\Math;
use Kronika\Utils\WeakRefsTrait;
use function Kronika\Utils\math;
use function Kronika\Utils\Math\double;
use function Kronika\Utils\Math\double_split;

/**
 * Represents the number of seconds counted from epoch of
 * "1970-01-01 00:00:00" in local time excluding the timezone.
 *
 * This is not the unix timestamp due to the representation of local time without a timezone.
 *
 * @psalm-type TMicrosecond=int<0,999999>
 */
final readonly class Instant
{
    /** @use WeakRefsTrait<static, int> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of Instant from a second and microsecond.
     *
     * ```
     * // 1767161730.004545
     * $instant = Instant::of(1767161730, 4545);
     * ```
     *
     * @param TMicrosecond $micro
     *
     * @throws Exception\InvalidValue
     */
    public static function of(int $second, int $micro = 0): self
    {
        return self::weak(second: $second, microsecond: $micro);
    }

    /**
     * Obtains an instance of Instant from a value of a second with microsecond.
     *
     * ```
     * // 1767161730.004545
     * $instant = Instant::ofValue('1767161730.004545');
     * ```
     *
     * @param numeric $value
     */
    public static function ofValue(float|int|string $value): self
    {
        return self::of(...double_split($value));
    }

    /**
     * @param TMicrosecond $microsecond
     *
     * @throws Exception\InvalidValue
     */
    private function __construct(
        private int $second,
        private int $microsecond,
    ){
        self::assertMicrosecond($microsecond);
    }

    /**
     * Returns an instance of ZonedDateTime from this instant and a given time-zone.
     *
     * ```
     * // 1767161730.004545
     * $this->at(new \DateTimeZone('+01:00'));  // 2025-12-31 12:15:30.004545 +01:00
     * ```
     */
    public function at(\DateTimeZone $timezone): ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this, $timezone);
    }

    /**
     * Returns the second counted from "1970-01-01 00:00:00".
     */
    public function second(): int
    {
        return $this->second;
    }

    /**
     * Returns the microsecond.
     *
     * @return TMicrosecond
     */
    public function microsecond(): int
    {
        return $this->microsecond;
    }

    /**
     * Returns the second with microsecond.
     *
     * ```
     * Instant::of(1767161730, 4545)->value();  // 1767161730.004545
     * ```
     */
    public function value(): float
    {
        return double([$this->second, $this->microsecond]);
    }

    /**
     * Adds an amount of days, hours, minutes and seconds to this instant.
     *
     * ```
     * // 1767161730.004545 + 2 days and 45 minutes
     * $this->add(Duration::of(days: 2, minutes: 45));  // 1767337230.004545
     * ```
     */
    public function add(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return self::of(...$this->math()->add($duration->inSeconds())->parts());
    }

    /**
     * Subtracts an amount of days, hours, minutes and seconds to this instant.
     *
     * ```
     * // 1767161730.004545 - 2 days and 45 minutes
     * $this->add(Duration::of(days: 2, minutes: 45));  // 1766979030.004545
     * ```
     */
    public function sub(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return self::of(...$this->math()->sub($duration->inSeconds())->parts());
    }

    /**
     * Returns an amount of days, hours, minutes and seconds from this instant to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767337230.004545
     * $duration = $this->until($other);
     * $duration->days();    // 2
     * $duration->hours();   // 0
     * $duration->minutes(); // 45
     * $duration->seconds(); // 0
     * ```
     * ```
     * // 1767161730.004545 vs 1766979030.004545
     * $duration = $this->until($other);
     * $duration->days();    // 0
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     */
    public function until(self $end): Duration
    {
        return $this->isBefore($end) ? $this->difference($end) : Duration::zero();
    }

    /**
     * Returns an amount of days, hours, minutes and seconds between this instant and another one.
     *
     * ```
     * // 1767161730.004545 vs 1767337230.004545
     * $duration = $this->difference($other);
     * $duration->days();    // 2
     * $duration->hours();   // 0
     * $duration->minutes(); // 45
     * $duration->seconds(); // 0
     * ```
     * ```
     * // 1767161730.004545 vs 1766979030.004545
     * $duration = $this->difference($other);
     * $duration->days();    // 2
     * $duration->hours();   // 0
     * $duration->minutes(); // 45
     * $duration->seconds(); // 0
     * ```
     */
    public function difference(self $other): Duration
    {
        return Duration::of(seconds: \abs($other->math()->sub($this->second, $this->microsecond)->integer()));
    }

    /**
     * Checks if this instant is before another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isBefore($other);  // false
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isBefore($other);  // true
     *
     * // 1767161730.004545 vs 1766979030.004545
     * $this->isBefore($other);  // false
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this instant is before or equal to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 1767161730.004545 vs 1766979030.004545
     * $this->isBeforeOrEqualTo($other);  // false
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this instant is equal to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isEqualTo($other);  // true
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isEqualTo($other);  // false
     * ```
     */
    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this instant is not equal to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isNotEqualTo($other);  // false
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isNotEqualTo($other);  // true
     * ```
     */
    public function isNotEqualTo(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this instant is after or equal to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isAfterOrEqualTo($other);  // true
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isAfterOrEqualTo($other);  // false
     *
     * // 1767161730.004545 vs 1766979030.004545
     * $this->isAfterOrEqualTo($other);  // true
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this instant is after another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isAfter($other);  // false
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isAfter($other);  // false
     *
     * // 1767161730.004545 vs 1766979030.004545
     * $this->isAfter($other);  // true
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this instant to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767337230.004545
     * $this->compareTo($other)->less();     // true
     * $this->compareTo($other)->equal();    // false
     * $this->compareTo($other)->greater();  // false
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
        return \sprintf('%06f', $this->value());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['second' => $this->value()];
    }

    /** @throws Exception\InvalidValue */
    private static function assertMicrosecond(int $microsecond): void
    {
        if ($microsecond < 0 || $microsecond > 999_999) {
            throw new Exception\InvalidValue("Microsecond must be between 0 and 999_999, got [$microsecond]");
        }
    }

    /** @internal */
    public function join(self $other): self
    {
        return self::of(...$this->math()->add($other->second, $other->microsecond)->parts());
    }

    private function math(): Math
    {
        return math($this->second, $this->microsecond, precision: 6);
    }
}
