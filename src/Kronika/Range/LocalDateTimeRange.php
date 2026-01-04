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
use Kronika\LocalDateTime;
use Kronika\Utils\RefTrait;

/**
 * Represents a date-time range with inclusive "start" and exclusive "end".
 *
 * ```
 * $start = LocalDateTime::parse('2025-12-30 12:15:30');
 * $end   = LocalDateTime::parse('2025-12-31 20:15:39');
 * $range = LocalDateTimeRange::of($start, $end);
 *
 * $range->contains($start);  // true
 * $range->contains($end);    // false
 * $range->duration();        // 1 day, 8 hours, 9 seconds
 *  ```
 *
 * @extends DateTimeRange<LocalDateTime>
 */
final readonly class LocalDateTimeRange extends DateTimeRange
{
    use RefTrait;

    /**
     * Obtains an instance of `LocalDateTimeRange`.
     *
     * @throws Exception\InvalidRange
     */
    public static function of(LocalDateTime $from, ?LocalDateTime $to): self
    {
        return self::ref(from: $from, to: $to ?? $from);
    }

    /**
     * Obtains an instance of `LocalDateTimeRange` where a given duration
     * is simultaneously subtracted from and added to a given date-time.
     *
     * ```
     * $datetime = LocalDateTime::parse('2025-12-30 12:30:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = LocalDateTimeRange::around($datetime, $duration);
     * $range->from();  // 2025-12-29 08:00:00
     * $range->to();    // 2025-12-31 17:00:00
     * ```
     */
    public static function around(LocalDateTime $datetime, Duration $duration): self
    {
        return self::of(from: $datetime->sub($duration), to: $datetime->add($duration));
    }

    /**
     * Obtains an instance of `LocalDateTimeRange` where a given duration
     * is simultaneously added to a given date-time.
     *
     * ```
     * $datetime = LocalDateTime::parse('2025-12-30 12:30:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = LocalDateTimeRange::after($datetime, $duration);
     * $range->from();  // 2025-12-30 12:30:00
     * $range->to();    // 2025-12-31 17:00:00
     * ```
     */
    public static function after(LocalDateTime $datetime, Duration $duration): self
    {
        return self::of(from: $datetime, to: $datetime->add($duration));
    }

    /**
     * Obtains an instance of `LocalDateTimeRange` where a given duration
     * is simultaneously subtracted from a given date-time.
     *
     * ```
     * $datetime = LocalDateTime::parse('2025-12-30 12:30:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = LocalDateTimeRange::before($datetime, $duration);
     * $range->from();  // 2025-12-29 08:00:00
     * $range->to();    // 2025-12-30 12:30:00
     * ```
     */
    public static function before(LocalDateTime $datetime, Duration $duration): self
    {
        return self::of(from: $datetime->sub($duration), to: $datetime);
    }

    /** @throws Exception\InvalidRange */
    private function __construct(
        private LocalDateTime $from,
        private LocalDateTime $to,
    ) {
        parent::__construct();
    }

    #[\Override]
    public function from(): LocalDateTime
    {
        return $this->from;
    }

    #[\Override]
    public function to(): LocalDateTime
    {
        return $this->to;
    }

    public function at(\DateTimeZone $timezone): ZonedDateTimeRange
    {
        return ZonedDateTimeRange::of(
            from: $this->from()->at($timezone),
            to: $this->to()->at($timezone),
        );
    }

    #[\Override]
    protected function instantiate(mixed $start, mixed $end): static
    {
        return self::of($start, $end);
    }
}
