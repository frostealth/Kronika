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
 * @extends DateTimeRange<ZonedDateTime>
 */
final readonly class ZonedDateTimeRange extends DateTimeRange
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
        parent::__construct();
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

    public function shift(\DateTimeZone $timezone): self
    {
        return self::of(
            from: $this->from()->shift($timezone),
            to: $this->to()->shift($timezone),
        );
    }

    #[\Override]
    protected function end(): ZonedDateTime
    {
        return $this->remember(static fn(self $that): ZonedDateTime => $that->to()->shift(
            $that->from()->timezone(),
        ), key: __METHOD__);
    }

    #[\Override]
    protected function instantiate(mixed $start, mixed $end): static
    {
        return self::of($start, $end);
    }
}
