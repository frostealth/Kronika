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
use Kronika\Utils\RefTrait;
use Kronika\ZonedDateTime;

/**
 * Represents a date-time range with inclusive "start" and exclusive "end".
 *
 * ```
 * $start = ZonedDateTime::parse('2025-12-30 12:15:30 +01:00');
 * $end   = ZonedDateTime::parse('2025-12-31 20:15:39 +01:00');
 * $range = ZonedDateTimeRange::of($start, $end);
 *
 * $range->contains($start);  // true
 * $range->contains($end);    // false
 * $range->duration();        // 1 day, 8 hours, 9 seconds
 * ```
 *
 * @implements Range<ZonedDateTime>
 */
final readonly class ZonedDateTimeRange implements Range
{
    use RefTrait;

    /**
     * Obtains an instance of `ZonedDateTimeRange`.
     *
     * @throws Exception\InvalidRange
     */
    public static function of(ZonedDateTime $from, ?ZonedDateTime $to): self
    {
        return self::ref(from: $from, to: $to ?? $from);
    }

    /**
     * Obtains an instance of `ZonedDateTimeRange` where a given duration
     * is simultaneously subtracted from and added to a given date-time.
     *
     * ```
     * $datetime = ZonedDateTime::parse('2025-12-30 12:30:00 +01:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = ZonedDateTimeRange::around($datetime, $duration);
     * $range->from();  // 2025-12-29 08:00:00 +01:00
     * $range->to();    // 2025-12-31 17:00:00 +01:00
     * ```
     */
    public static function around(ZonedDateTime $datetime, Duration $duration): self
    {
        return self::of(from: $datetime->sub($duration), to: $datetime->add($duration));
    }

    /**
     * Obtains an instance of `ZonedDateTimeRange` where a given duration
     * is simultaneously added to a given date-time.
     *
     * ```
     * $datetime = ZonedDateTime::parse('2025-12-30 12:30:00 +01:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = ZonedDateTimeRange::after($datetime, $duration);
     * $range->from();  // 2025-12-30 12:30:00 +01:00
     * $range->to();    // 2025-12-31 17:00:00 +01:00
     * ```
     */
    public static function after(ZonedDateTime $datetime, Duration $duration): self
    {
        return self::of(from: $datetime, to: $datetime->add($duration));
    }

    /**
     * Obtains an instance of `ZonedDateTimeRange` where a given duration
     * is simultaneously subtracted from a given date-time.
     *
     * ```
     * $datetime = ZonedDateTime::parse('2025-12-30 12:30:00 +01:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = ZonedDateTimeRange::around($datetime, $duration);
     * $range->from();  // 2025-12-29 08:00:00 +01:00
     * $range->to();    // 2025-12-30 12:30:00 +01:00
     * ```
     */
    public static function before(ZonedDateTime $datetime, Duration $duration): self
    {
        return self::of(from: $datetime->sub($duration), to: $datetime);
    }

    /** @throws Exception\InvalidRange */
    private function __construct(
        private ZonedDateTime $from,
        private ZonedDateTime $to,
    ) {
        if ($this->from()->isAfter($this->to())) {
            throw new Exception\InvalidRange(
                \sprintf('Invalid range: [%s] – [%s]', $this->from(), $this->to()),
            );
        }
    }

    #[\Override]
    public function from(): ZonedDateTime
    {
        return $this->from;
    }

    #[\Override]
    public function to(): ZonedDateTime
    {
        return $this->to;
    }

    #[\Override]
    public function duration(): Duration
    {
        return $this->from()->until($this->to());
    }

    #[\Override]
    public function isZero(): bool
    {
        return $this->from()->is($this->to());
    }

    /**
     * Resets the microsecond to 0.
     */
    public function resetMicro(): self
    {
        return self::of(
            from: $this->from()->resetMicro(),
            to: $this->to()->resetMicro()
        );
    }

    /**
     * Resets the second and microsecond to 0.
     */
    public function resetSecond(): self
    {
        return self::of(
            from: $this->from()->resetSecond(),
            to: $this->to()->resetSecond()
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
    public function contains(self|ZonedDateTime $other): bool
    {
        if ($other instanceof self) {
            return $this->contains($other->from())
                && $this->contains($other->to())
            ;
        }
        if ($this->isZero()) {
            return $this->from()->is($other);
        }

        return $this->from()->isBeforeOrEqualTo($other)
            && $this->to()->isAfter($other);
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
    public function overlaps(self $other): bool
    {
        return $this->from()->isBefore($other->to())
            && $this->to()->isAfter($other->from());
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
    public function abuts(self $other): bool
    {
        return $this->from()->is($other->to())
            || $this->to()->is($other->from());
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
    public function isDuring(self $other): bool
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
    public function is(self $other): bool
    {
        return $this->from()->is($other->from())
            && $this->to()->is($other->to());
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
    public function isNot(self $other): bool
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
    public function isBefore(self|ZonedDateTime $other): bool
    {
        $other = $other instanceof self ? $other->from() : $other;

        return $this->to()->isBefore($other);
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
    public function isAfter(self|ZonedDateTime $other): bool
    {
        $other = $other instanceof self ? $other->to() : $other;

        return $this->from()->isAfter($other);
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
            yield self::of(from: $start, to: $end);
        } while ($this->contains($start = $end));
    }

    #[\Override]
    public function each(Duration $step): iterable
    {
        $current = $this->from();
        if ($step->isZero()) {
            yield $current;
            return;
        }

        do {
            yield $current;
        } while ($this->contains($current = $current->add($step)));
    }

    public function shift(\DateTimeZone $timezone): self
    {
        return self::of(
            from: $this->from()->shift($timezone),
            to: $this->to()->shift($timezone),
        );
    }

    /**
     * Obtains an instance of `DateRange` from this range in a given timezone.
     */
    public function toDateRange(\DateTimeZone $timezone): DateRange
    {
        return DateRange::of(
            from: $this->from()->shift($timezone)->date(),
            to: $this->to()->shift($timezone)->date(),
        );
    }

    /**
     * Obtains an instance of `\DatePeriod` from this range.
     */
    public function toNative(Duration|\DateInterval $step): \DatePeriod
    {
        return new \DatePeriod(
            start: $this->from(),
            interval: $step instanceof Duration ? $step->toNative() : $step,
            end: $this->to(),
        );
    }
}
