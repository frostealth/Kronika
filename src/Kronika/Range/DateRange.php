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

use Kronika\Date;
use Kronika\Duration;
use Kronika\Range;
use Kronika\Utils\RefTrait;

/**
 * Represents a date range with inclusive "start" and exclusive "end".
 *
 * ```
 * $start = Date::of(2025, 12, 01);
 * $end   = Date::of(2025, 12, 31);
 * $range = DateRange::of($start, $end);
 *
 * $range->contains($start);  // true
 * $range->contains($end);    // false
 * $range->duration();        // 30 days
 * ```
 *
 * @implements Range<Date>
 */
final readonly class DateRange implements Range
{
    use RefTrait;

    /**
     * Obtains an instance of `DateRange`.
     *
     * @throws Exception\InvalidRange
     */
    public static function of(Date $from, ?Date $to): self
    {
        return self::ref(from: $from, to: $to ?? $from);
    }

    /**
     * Obtains an instance of `DateRange` where a given duration
     * is simultaneously subtracted from and added to a given date.
     *
     * ```
     * $date = Date::of(2025, 12, 15);
     * $duration = Duration::of(days: 5);
     *
     * $range = DateRange::around($date, $duration);
     * $range->from();  // 2025-12-10
     * $range->to();    // 2025-12-20
     * ```
     */
    public static function around(Date $date, Duration $duration): self
    {
        return self::of(from: $date->sub($duration), to: $date->add($duration));
    }

    /**
     * Obtains an instance of `DateRange` where a given duration
     * is simultaneously added to a given date.
     *
     * ```
     * $date = Date::of(2025, 12, 15);
     * $duration = Duration::of(days: 5);
     *
     * $range = DateRange::after($date, $duration);
     * $range->from();  // 2025-12-10
     * $range->to();    // 2025-12-20
     * ```
     */
    public static function after(Date $date, Duration $duration): self
    {
        return self::of(from: $date, to: $date->add($duration));
    }

    /**
     * Obtains an instance of `DateRange` where a given duration
     * is simultaneously subtracted from a given date.
     *
     * ```
     * $date = Date::of(2025, 12, 15);
     * $duration = Duration::of(days: 5);
     *
     * $range = DateRange::before($date, $duration);
     * $range->from();  // 2025-12-10
     * $range->to();    // 2025-12-20
     * ```
     */
    public static function before(Date $date, Duration $duration): self
    {
        return self::of(from: $date->sub($duration), to: $date);
    }

    /** @throws Exception\InvalidRange */
    private function __construct(
        private Date $from,
        private Date $to,
    ) {
        if ($from->isAfter($to)) {
            throw new Exception\InvalidRange(\sprintf('Invalid range: [%s] – [%s]', $from, $to));
        }
    }

    #[\Override]
    public function from(): Date
    {
        return $this->from;
    }

    #[\Override]
    public function to(): Date
    {
        return $this->to;
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
     * Checks if this date range contains another one or a date.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-15 – 2025-12-19
     * $this->contains($other);  // true
     *
     * // $other: 2025-12-18 – 2025-12-25
     * $this->contains($other);  // false
     *
     * // $date: 2025-12-17
     * $this->contains($date);  // true
     *
     * // $date: 2025-12-21
     * $this->contains($date);  // false
     * ```
     */
    public function contains(self|Date $date): bool
    {
        if ($date instanceof self) {
            return $this->contains($date->from())
                && $this->to()->isAfterOrEqualTo($date->to());
        }
        if ($this->isZero()) {
            return $this->from()->is($date);
        }

        return $this->from()->isBeforeOrEqualTo($date)
            && $this->to()->isAfter($date);
    }

    /**
     * Checks if this date range overlaps with another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-19 – 2025-12-25
     * $this->overlaps($foo);  // true
     *
     * // $other: 2025-12-21 – 2025-12-30
     * $this->overlaps($bar);  // false
     * ```
     */
    public function overlaps(self $other): bool
    {
        return $this->from()->isBefore($other->to())
            && $this->to()->isAfter($other->from());
    }

    /**
     * Checks if this date range abuts with another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-20 – 2025-12-25
     * $this->abuts($other);  // true
     *
     * // $other: 2025-12-21 – 2025-12-26
     * $this->abuts($other);  // false
     * ```
     */
    public function abuts(self $other): bool
    {
        return $this->from()->is($other->to())
            || $this->to()->is($other->from());
    }

    /**
     * Checks if this date range is fully contained by another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-10 – 2025-12-30
     * $this->isDuring($other);  // true
     *
     * // $other: 2025-12-17 – 2025-12-22
     * $this->isDuring($other);  // false
     * ```
     */
    public function isDuring(self $other): bool
    {
        return $other->contains($this);
    }

    /**
     * Checks if this date range is equal to another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-15 – 2025-12-20
     * $this->is($other);  // true
     *
     * // $other: 2025-12-10 – 2025-12-17
     * $this->is($other);  // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->from()->is($other->from())
            && $this->to()->is($other->to());
    }

    /**
     * Checks if this date range is not equal to another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-15 – 2025-12-20
     * $this->isNot($other);  // false
     *
     * // $other: 2025-12-10 – 2025-12-17
     * $this->isNot($other);  // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return ! $this->is($other);
    }

    /**
     * Checks if this date range is before another one or a date.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-25 – 2025-12-30
     * $this->isBefore($other);  // true
     *
     * // $other: 2025-12-17 – 2025-12-22
     * $this->isBefore($other);  // false
     *
     * // $date: 2025-12-25
     * $this->isBefore($date);  // true
     * ```
     */
    public function isBefore(self|Date $other): bool
    {
        $other = $other instanceof self ? $other->from() : $other;

        return $this->to()->isBefore($other);
    }

    /**
     * Checks if this date range is after another one or a date.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-05 – 2025-12-10
     * $this->isAfter($other);  // true
     *
     * // $other: 2025-12-12 – 2025-12-17
     * $this->isAfter($other);  // false
     *
     * // $date: 2025-12-12
     * $this->isAfter($date);  // true
     * ```
     */
    public function isAfter(self|Date $other): bool
    {
        $other = $other instanceof self ? $other->to() : $other;

        return $this->from()->isAfter($other);
    }

    /**
     * Computes the intersection between this date range and another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-17 – 2025-12-25
     * $this->intersection($other);
     * // result: 2025-12-17 – 2025-12-20
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
     * Computes the gap between this date range and another one.
     *
     * ```
     * // $this: 2025-12-15 – 2025-12-20
     * // $other: 2025-12-25 – 2025-12-30
     * $this->gap($other);
     * // result: 2025-12-20 – 2025-12-25
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
        $step = $step->roundToDays();
        if ($step->isZero() || $step->isGreaterThanOrEqualTo($this->duration())) {
            yield $this;
            return;
        }

        do {
            $end = $this->to()->isAfter($end = $start->add($step)) ? $end : $this->to();
            yield self::of(from: $start, to: $end);
        } while ($this->contains($start = $end));
    }

    #[\Override]
    public function each(Duration $step): iterable
    {
        $step = $step->roundToDays();
        $current = $this->from();
        if ($step->isZero()) {
            yield $current;
            return;
        }

        do {
            yield $current;
        } while ($this->contains($current = $current->add($step)));
    }
}
