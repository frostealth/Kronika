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

namespace Kronika\Range;

use Kronika\Duration;
use Kronika\Range;
use Kronika\Time;
use Kronika\Utils\RefTrait;

/**
 * Represents a time of day range with inclusive "start" and exclusive "end".
 *
 * ```
 * $start  = Time::of(12, 15, 30);
 * $end    = Time::of(20, 15, 30);
 * $range  = TimeRange::of($start, $end);
 *
 * $range->contains($start);  // true
 * $range->contains($end);    // false
 * $range->duration();        // 8 hours
 * ```
 *
 * @implements Range<Time>
 */
final readonly class TimeRange implements Range
{
    use RefTrait;

    /**
     * Obtains an instance of `TimeRange`.
     *
     * @throws Exception\InvalidRange
     */
    public static function of(Time $from, ?Time $to): self
    {
        return self::ref(from: $from, to: $to ?? $from);
    }

    /** @throws Exception\InvalidRange */
    private function __construct(
        private Time $from,
        private Time $to,
    ) {
        if ($from->isAfter($to)) {
            throw new Exception\InvalidRange(\sprintf('Invalid range: [%s] – [%s]', $from, $to));
        }
    }

    #[\Override]
    public function from(): Time
    {
        return $this->from;
    }

    #[\Override]
    public function to(): Time
    {
        return $this->to;
    }

    /**
     * Resets the microsecond to 0.
     *
     * ```
     * // 12:15:30.999999 – 20:15:10.999999
     * $this->resetMicro();
     * // 12:15:30.000000 – 20:15:10.000000
     * ```
     */
    public function resetMicro(): self
    {
        return self::of(
            from: $this->from()->resetMicro(),
            to: $this->to()->resetMicro(),
        );
    }

    /**
     * Resets the second and microsecond to 0.
     *
     * ```
     * // 12:15:30.999999 – 20:15:10.999999
     * $this->resetSecond();
     * // 12:15:00.000000 – 20:15:00.000000
     * ```
     */
    public function resetSecond(): self
    {
        return self::of(
            from: $this->from()->resetSecond(),
            to: $this->to()->resetSecond(),
        );
    }

    #[\Override]
    public function isZero(): bool
    {
        return $this->from()->is($this->to());
    }

    #[\Override]
    public function duration(): Duration
    {
        return $this->from()->until($this->to());
    }

    /**
     * Checks if this time range contains another one or a time of day.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 12:15 – 14:30
     * $this->contains($other);  // true
     *
     * // $other: 14:30 – 21:30
     * $this->contains($other);  // false
     *
     * // $time: 15:00
     * $this->contains($time);  // true
     *
     * // $time: 21:30
     * $this->contains($time);  // false
     * ```
     */
    public function contains(self|Time $time): bool
    {
        if ($time instanceof self) {
            return $this->contains($time->from())
                && $this->to()->isAfterOrEqualTo($time->to());
        }
        if ($this->isZero()) {
            return $this->from()->is($time);
        }

        return $this->from()->isBeforeOrEqualTo($time)
            && $this->to()->isAfter($time);
    }

    /**
     * Checks if this time range overlaps with another one.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 16:30 – 22:00
     * $this->overlaps($foo);  // true
     *
     * // $other: 22:00 – 23:30
     * $this->overlaps($bar);  // false
     * ```
     */
    public function overlaps(self $other): bool
    {
        return $this->from()->isBefore($other->to())
            && $this->to()->isAfter($other->from());
    }

    /**
     * Checks if this time range abuts with another one.
     *
     * ```
     * // $this: 12:15 - 20:15
     * // $other: 20:15 - 22:00
     * $this->abuts($other);  // true
     *
     * // $other: 20:20 - 22:00
     * $this->abuts($other);  // false
     * ```
     */
    public function abuts(self $other): bool
    {
        return $this->from()->is($other->to())
            || $this->to()->is($other->from());
    }

    /**
     * Checks if this time range is fully contained by another one.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 10:00 – 22:00
     * $this->isDuring($other);  // true
     *
     * // $other: 14:00 – 23:00
     * $this->isDuring($other);  // false
     * ```
     */
    public function isDuring(self $other): bool
    {
        return $other->contains($this);
    }

    /**
     * Checks if this time range is equal to another one.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 12:15 – 20:15
     * $this->is($other);  // true
     *
     * // $other: 16:30 – 20:15
     * $this->is($other);  // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->from()->is($other->from())
            && $this->to()->is($other->to());
    }

    /**
     * Checks if this time range is not equal to another one.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 12:15 – 20:15
     * $this->isNot($other);  // false
     *
     * // $other: 16:30 – 20:15
     * $this->isNot($other);  // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return ! $this->is($other);
    }

    /**
     * Checks if this time range is before another one or a time of day.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 21:30 – 23:30
     * $this->isBefore($other);  // true
     *
     * // $other: 16:00 – 21:00
     * $this->isBefore($other);  // false
     *
     * // $time: 21:30
     * $this->isBefore($time);  // true
     * ```
     */
    public function isBefore(self|Time $other): bool
    {
        $other = $other instanceof self ? $other->from() : $other;

        return $this->to()->isBefore($other);
    }

    /**
     * Checks if this time range is after another one or a time.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 10:00 – 11:00
     * $this->isAfter($other);  // true
     *
     * // $other: 12:00 – 13:00
     * $this->isAfter($other);  // false
     *
     * // $time: 10:00
     * $this->isAfter($time);  // true
     * ```
     */
    public function isAfter(self|Time $other): bool
    {
        $other = $other instanceof self ? $other->to() : $other;

        return $this->from()->isAfter($other);
    }

    /**
     * Computes the intersection between this time range and another one.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 16:30 – 22:00
     * $this->intersection($other);
     * // result: 16:30 – 20:15
     * ```
     *
     * @throws Exception\NoOverlap if the ranges don't intersect each other
     */
    public function intersection(self $other): self
    {
        if (! $this->overlaps($other)) {
            throw new Exception\NoOverlap('Ranges do not intersect each other');
        }

        return self::of(
            from: $this->from()->isAfter($other->from()) ? $this->from() : $other->from(),
            to: $this->to()->isBefore($other->to()) ? $this->to() : $other->to(),
        );
    }

    /**
     * Computes the gap between this time range and another one.
     *
     * ```
     * // $this: 12:15 – 20:15
     * // $other: 22:00 – 23:30
     * $this->gap($other);
     * // result: 20:15 – 22:00
     * ```
     *
     * @throws Exception\Overlap if the ranges intersect each other
     */
    public function gap(self $other): self
    {
        if ($this->overlaps($other)) {
            throw new Exception\Overlap('Ranges intersect each other');
        }

        return $this->to()->isBeforeOrEqualTo($other->from())
            ? self::of(from: $this->to(), to: $other->from())
            : self::of(from: $other->to(), to: $this->from());
    }

    #[\Override]
    public function split(Duration $step): iterable
    {
        $start = $this->from();
        if ($step->isZero() || $step->isGreaterThanOrEqualTo($this->duration())) {
            yield $this;
            return;
        }

        do {
            $end = $this->to()->isAfter($end = $start->add($step)) ? $end : $this->to();
            $end = $start->isBefore($end) ? $end : $this->to();
            yield self::of(from: $start, to: $end);
        } while ($this->contains($start = $end));
    }

    #[\Override]
    public function each(Duration $step): iterable
    {
        $step = $step->dropToHours();
        $current = $this->from();
        if ($step->isZero()) {
            yield $current;
            return;
        }

        do {
            yield $current;
        } while (
            $this->contains($current = $current->add($step))
            && $this->from()->isNot($current)
        );
    }
}
