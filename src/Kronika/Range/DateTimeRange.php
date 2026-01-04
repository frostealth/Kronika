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

use Kronika\DateTime;
use Kronika\Duration;
use Kronika\Range;

/**
 * Represents a date-time range with inclusive "start" and exclusive "end".
 *
 * @template-covariant TDateTime of DateTime
 * @implements Range<TDateTime>
 *
 * @psalm-inheritors LocalDateTimeRange|ZonedDateTimeRange
 */
abstract readonly class DateTimeRange implements Range
{
    /** @throws Exception\InvalidRange */
    protected function __construct()
    {
        if ($this->from()->isAfter($this->to())) {
            throw new Exception\InvalidRange(\sprintf('Invalid range: [%s] – [%s]', $this->from(), $this->to()));
        }
    }

    #[\Override]
    abstract public function from(): DateTime;

    #[\Override]
    abstract public function to(): DateTime;

    #[\Override]
    final public function duration(): Duration
    {
        return $this->from()->until($this->to());
    }

    #[\Override]
    final public function isZero(): bool
    {
        return $this->from()->is($this->to());
    }

    /**
     * Resets the microsecond to 0.
     */
    final public function resetMicro(): static
    {
        return $this->instantiate(
            start: $this->from()->resetMicro(),
            end: $this->to()->resetMicro(),
        );
    }

    /**
     * Resets the second and microsecond to 0.
     */
    final public function resetSecond(): static
    {
        return $this->instantiate(
            start: $this->from()->resetSecond(),
            end: $this->to()->resetSecond(),
        );
    }

    /**
     * Checks if this date-time range contains another one or a date-time.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-15 12:15 – 2025-12-20 14:30
     * $this->contains($other);  // true
     *
     * // $other: 2025-12-20 14:30 – 2025-12-25 21:30
     * $this->contains($other);  // false
     *
     * // $datetime: 2025-12-18 15:00
     * $this->contains($datetime);  // true
     *
     * // $datetime: 2025-12-20 21:30
     * $this->contains($datetime);  // false
     * ```
     */
    final public function contains(DateTimeRange|DateTime $other): bool
    {
        if ($other instanceof self) {
            return $this->contains($other->start())
                && $this->contains($other->end())
            ;
        }
        if ($this->isZero()) {
            return $this->start()->is($other);
        }

        return $this->start()->isBeforeOrEqualTo($other)
            && $this->end()->isAfter($other);
    }

    /**
     * Checks if this date-time range overlaps with another one.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-20 16:30 – 2025-12-25 22:00
     * $this->overlaps($foo);  // true
     *
     * // $other: 2025-12-20 22:00 – 2025-12-25 23:30
     * $this->overlaps($bar);  // false
     * ```
     */
    final public function overlaps(DateTimeRange $other): bool
    {
        return $this->start()->isBefore($other->end())
            && $this->end()->isAfter($other->start());
    }

    /**
     * Checks if this date-time range abuts with another one.
     *
     * ```
     * // $this: 2025-12-15 12:15 - 2025-12-20 20:15
     * // $other: 2025-12-20 20:15 - 2025-12-25 22:00
     * $this->abuts($other);  // true
     *
     * // $other: 2025-12-20 20:20 - 2025-12-25 22:00
     * $this->abuts($other);  // false
     * ```
     */
    final public function abuts(DateTimeRange $other): bool
    {
        return $this->start()->is($other->end())
            || $this->end()->is($other->start());
    }

    /**
     * Checks if this date-time range is fully contained by another one.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-10 10:00 – 2025-12-25 22:00
     * $this->isDuring($other);  // true
     *
     * // $other: 2025-12-15 14:00 – 2025-12-25 23:00
     * $this->isDuring($other);  // false
     * ```
     */
    final public function isDuring(DateTimeRange $other): bool
    {
        return $other->contains($this);
    }

    /**
     * Checks if this date-time range is equal to another one.
     *
     * ```
     * // $this: 2025-12-20 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-20 12:15 – 2025-12-20 20:15
     * $this->is($other);  // true
     *
     * // $other: 2025-12-21 16:30 – 2025-12-25 20:15
     * $this->is($other);  // false
     * ```
     */
    final public function is(DateTimeRange $other): bool
    {
        return $this->start()->is($other->start())
            && $this->end()->is($other->end());
    }

    /**
     * Checks if this date-time range is not equal to another one.
     *
     * ```
     * // $this: 2025-12-20 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-20 12:15 – 2025-12-20 20:15
     * $this->isNot($other);  // true
     *
     * // $other: 2025-12-21 16:30 – 2025-12-25 20:15
     * $this->isNot($other);  // false
     * ```
     */
    final public function isNot(DateTimeRange $other): bool
    {
        return ! $this->is($other);
    }

    /**
     * Checks if this date-time range is before another one or a date-time.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-25 21:30 – 2025-12-30 23:30
     * $this->isBefore($other);  // true
     *
     * // $other: 2025-12-20 16:00 – 2025-12-25 21:00
     * $this->isBefore($other);  // false
     *
     * // $datetime: 2025-12-25 21:30
     * $this->isBefore($datetime);  // true
     * ```
     */
    final public function isBefore(DateTimeRange|DateTime $other): bool
    {
        $other = $other instanceof self ? $other->start() : $other;

        return $this->end()->isBefore($other);
    }

    /**
     * Checks if this date-time range is after another one or a date-time.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-01 10:00 – 2025-12-15 11:00
     * $this->isAfter($other);  // true
     *
     * // $other: 2025-12-15 12:00 – 2025-12-15 13:00
     * $this->isAfter($other);  // false
     *
     * // $datetime: 2025-12-25 10:00
     * $this->isAfter($datetime);  // true
     * ```
     */
    final public function isAfter(DateTimeRange|DateTime $other): bool
    {
        $other = $other instanceof self ? $other->end() : $other;

        return $this->start()->isAfter($other);
    }

    /**
     * Computes the intersection between this date-time range and another one.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-20 16:30 – 2025-12-25 22:00
     * $this->intersection($other);
     * // result: 2025-12-20 16:30 – 2025-12-20 20:15
     * ```
     *
     * @throws Exception\NoOverlap if the ranges don't intersect each other
     */
    final public function intersection(DateTimeRange $other): static
    {
        if (! $this->overlaps($other)) {
            throw new Exception\NoOverlap('Ranges do not intersect each other');
        }

        return $this->instantiate(
            $this->start()->add($this->start()->until($other->start())),
            $this->end()->sub($other->end()->until($this->end())),
        );
    }

    /**
     * Computes the gap between this date-time range and another one.
     *
     * ```
     * // $this: 2025-12-15 12:15 – 2025-12-20 20:15
     * // $other: 2025-12-20 22:00 – 2025-12-25 23:30
     * $this->gap($other);
     * // result: 2025-12-20 20:15 – 2025-12-20 22:00
     * ```
     *
     * @throws Exception\Overlap if the ranges intersect each other
     */
    final public function gap(DateTimeRange $other): static
    {
        if ($this->overlaps($other)) {
            throw new Exception\Overlap('Ranges intersect each other');
        }

        return $this->end()->isBeforeOrEqualTo($other->start())
            ? $this->instantiate($this->end(), $this->end()->add($this->end()->until($other->start())))
            : $this->instantiate($this->start()->sub($other->end()->until($this->start())), $this->start());
    }

    /** @return iterable<static> */
    #[\Override]
    final public function split(Duration $step): iterable
    {
        $start = $this->start();
        if ($step->isZero() || $step->isGreaterThanOrEqualTo($this->duration())) {
            yield $this;
            return;
        }

        do {
            $end = $this->end()->isAfter($end = $start->add($step)) ? $end : $this->end();
            yield $this->instantiate($start, $end);
        } while ($this->contains($start = $end));
    }

    #[\Override]
    final public function each(Duration $step): iterable
    {
        $current = $this->start();
        if ($step->isZero()) {
            yield $current;
            return;
        }

        do {
            yield $current;
        } while ($this->contains($current = $current->add($step)));
    }

    /**
     * Returns an instance of `DateRange` from this range.
     */
    final public function toDateRange(): DateRange
    {
        return DateRange::of(
            from: $this->start()->date(),
            to: $this->end()->date(),
        );
    }

    /**
     * Returns the normalized start of the range.
     *
     * @return TDateTime
     */
    protected function start(): DateTime
    {
        return $this->from();
    }

    /**
     * Returns the normalized end of the range.
     *
     * @return TDateTime
     */
    protected function end(): DateTime
    {
        return $this->to();
    }

    abstract protected function instantiate(mixed $start, mixed $end): static;
}
