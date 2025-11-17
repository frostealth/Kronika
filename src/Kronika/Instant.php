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
use Kronika\Utils\RefTrait;
use Kronika\Utils\RescueTrait;
use function Kronika\Utils\math;
use function Kronika\Utils\Math\double;
use function Kronika\Utils\Math\double_split;

/**
 * Represents the number of seconds counted from epoch of
 * "1970-01-01 00:00:00" in local time excluding the timezone.
 *
 * This is not the unix timestamp due to the representation of local time without a timezone.
 *
 * @method static static|null tryOf(mixed $second, mixed $micro = 0)
 * @method static static|null tryOfValue(mixed $value)
 *
 * @psalm-type TMicrosecond=int<0,999999>
 */
final readonly class Instant
{
    use RefTrait;
    use RescueTrait;

    /**
     * Obtains an instance of `Instant` from a second and microsecond.
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
        return self::ref(second: $second, microsecond: $micro);
    }

    /**
     * Obtains an instance of `Instant` from a value of a second with microsecond.
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
     * Combines this instant with a given time-zone to create an instance of `ZonedDateTime`.
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
     * Resets the microsecond to 0.
     */
    public function resetMicro(): self
    {
        return $this->remember(static fn(self $that): self => self::of(second: $that->second), key: __METHOD__);
    }

    /**
     * Resets the second and microsecond to 0.
     */
    public function resetSecond(): self
    {
        return $this->remember(
            static fn(self $that): self => self::of(second: $that->second - ($that->second % 60)),
            key: __METHOD__,
        );
    }

    /**
     * Resets the minute, second and microsecond to 0.
     */
    public function resetMinute(): self
    {
        return $this->remember(
            static fn(self $that): self => self::of(second: $that->second - ($that->second % 3600)),
            key: __METHOD__,
        );
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
        return $this->remember(static fn(self $that): float => double([
            $that->second,
            $that->microsecond,
        ]), key: __METHOD__);
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
     * Subtracts an amount of days, hours, minutes and seconds from this instant.
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
     * Calculates the duration from this instant to another one.
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
    public function until(self $end, Precision $precision = Precision::Micro): Duration
    {
        return $this->isBefore($end, $precision) ? $this->difference($end, $precision) : Duration::zero();
    }

    /**
     * Calculates the duration between this instant and another one.
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
    public function difference(self $other, Precision $precision = Precision::Micro): Duration
    {
        $that = $this->applyPrecision($precision);
        $other = $other->applyPrecision($precision);

        return Duration::of(seconds: \abs($other->math()->sub($that->second, $that->microsecond)->integer()));
    }

    /**
     * Checks if this instant is equal to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->is($other);  // true
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->is($other);  // false
     * ```
     */
    public function is(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    /**
     * Checks if this instant is not equal to another one.
     *
     * ```
     * // 1767161730.004545 vs 1767161730.004545
     * $this->isNot($other);  // false
     *
     * // 1767161730.004545 vs 1767337230.004545
     * $this->isNot($other);  // true
     * ```
     */
    public function isNot(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
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
    public function isBefore(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
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
    public function isBeforeOrEqualTo(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    /** @deprecated {@see self::is()} */
    public function isEqualTo(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->is($other, $precision);
    }

    /** @deprecated {@see self::isNot()} */
    public function isNotEqualTo(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->isNot($other, $precision);
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
    public function isAfterOrEqualTo(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
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
    public function isAfter(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
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
    public function compareTo(self $other, Precision $precision = Precision::Micro): Compared
    {
        return Compared::of($this->applyPrecision($precision) <=> $other->applyPrecision($precision));
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
    public function _join(self $other): self
    {
        return self::of(...$this->math()->add($other->second, $other->microsecond)->parts());
    }

    private function applyPrecision(Precision $precision): self
    {
        return match ($precision) {
            Precision::Micro => $this,
            Precision::Second => $this->resetMicro(),
            Precision::Minute => $this->resetSecond(),
        };
    }

    private function math(): Math
    {
        return math($this->second, $this->microsecond, precision: 6);
    }
}
