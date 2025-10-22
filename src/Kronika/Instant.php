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

use Kronika\Utils\Comparison;
use Kronika\Utils\Math\Math;
use function Kronika\Utils\math;

/**
 * The number of seconds counted from epoch of
 * "1970-01-01 00:00:00" in local time excluding the timezone.
 *
 * This is not the unix timestamp due to the representation of local time without a timezone.
 *
 * @psalm-type TMicrosecond=int<0,999999>
 */
final readonly class Instant
{
    /** @param TMicrosecond $micro */
    public static function of(int $second, int $micro = 0): self
    {
        return new self($second, $micro);
    }

    /** @param numeric $value */
    public static function ofValue(float|int|string $value): self
    {
        if (\is_int($value)) {
            return self::of($value);
        }

        \assert(\is_numeric($value));
        [$second, $micro] = \sscanf((string) $value, '%d.%06d');

        return self::of($second, $micro ?? 0);
    }

    /** @param TMicrosecond $microsecond */
    private function __construct(
        private int $second,
        private int $microsecond,
    ){
        \assert($microsecond >= 0 && $microsecond < 1_000_000);
    }

    public function atTimezone(\DateTimeZone $timezone): ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this, $timezone);
    }

    public function second(): int
    {
        return $this->second;
    }

    /** @return TMicrosecond */
    public function microsecond(): int
    {
        return $this->microsecond;
    }

    public function add(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return self::of(...$this->math()->add($duration->inSeconds())->parts());
    }

    public function sub(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return self::of(...$this->math()->sub($duration->inSeconds())->parts());
    }

    public function until(self $end): Duration
    {
        if ($this->compareTo($end)->lessOrEqual()) {
            return Duration::zero();
        }

        return $this->diff($end);
    }

    public function diff(self $other): Duration  // @todo: or "between()"?
    {
        return Duration::of(seconds: \abs($other->math()->sub($this->second, $this->microsecond)->integer()));
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    public function isBeforeOrEqual(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    public function isNotEqualTo(self $other): bool
    {
        return ! $this->isEqualTo($other);
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    public function isAfterOrEqual(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    public function compareTo(self $other): Comparison
    {
        return Comparison::compare($this->value(), $other->value());
    }

    /** @internal */
    public function merge(self ...$others): self
    {
        $result = \array_reduce($others, static fn(Math $carry, self $other): Math => $carry->add(
            $other->second,
            $other->microsecond,
        ), $this->math());

        return self::of(...$result->parts());
    }

    private function math(): Math
    {
        return math($this->second, $this->microsecond, precision: 6);
    }

    /** @return numeric-string */
    private function value(): string
    {
        if ($this->microsecond === 0) {
            return (string) $this->second;
        }

        return \sprintf('%d.%06d', $this->second, $this->microsecond);
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return $this->value();
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['second' => (float) $this->value()];
    }
}
