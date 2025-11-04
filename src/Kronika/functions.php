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

use Kronika\Format\Formatter;

if (! \function_exists('\\Kronika\\now')) {
    /**
     * Returns the current time with a given or system time-zone from the global clock.
     *
     * @example
     * ```
     * // 2025-12-31 11:15:30.000155 UTC
     * $datetime = now();
     *
     * // 2025-12-31 12:15:30.000155 +01:00
     * $datetime = now(new \DateTimeZone('+01:00'));
     * $datetime->resetMicro();      // 2025-12-31 12:15:30.000000 +01:00
     * $datetime->resetSecond();     // 2025-12-31 12:15:00.000000 +01:00
     * $datetime->toLocalDateTime(); // 2025-12-31 12:15:30.000155
     * $datetime->date();            // 2025-12-31
     * $datetime->time();            // 12:15:30.000155
     * ```
     */
    function now(?\DateTimeZone $timezone = null): ZonedDateTime
    {
        return \is_null($timezone) ? clock()->now() : clock()->now()->shiftTimezone($timezone);
    }
}

if (! \function_exists('\\Kronika\\clock')) {
    /**
     * Global clock.
     *
     * @see now()
     */
    function clock(?Clock $clock = null): Clock
    {
        static $instance = $clock ?? new Clock\SystemClock();

        return $instance = $clock ?? $instance;
    }
}

if (! \function_exists('\\Kronika\\earliest')) {
    /**
     * Returns the earliest date-time.
     *
     * ```
     * // $first: 2025-12-31 00:00:00
     * // $second: 2025-12-30 23:00:00
     * earliest($first, $second);  // 2025-12-30 23:00:00
     * ```
     *
     * @template T of DateTime
     *
     * @param T $first
     * @param T ...$others
     *
     * @return T
     */
    function earliest(DateTime $first, DateTime ...$others): DateTime
    {
        return chronologize($first, ...$others)[0];
    }
}

if (! \function_exists('\\Kronika\\latest')) {
    /**
     * Returns the latest date-time.
     *
     * ```
     * // $first: 2025-12-31 00:00:00
     * // $second: 2025-12-30 23:00:00
     * latest($first, $second);  // 2025-12-31 00:00:00
     * ```
     *
     * @template T of DateTime
     *
     * @param T $first
     * @param T ...$others
     *
     * @return T
     */
    function latest(DateTime $first, DateTime ...$others): DateTime
    {
        return \array_reverse(chronologize($first, ...$others))[0];
    }
}

if (! \function_exists('\\Kronika\\chronologize')) {
    /**
     * Sorts in chronological order.
     *
     * @template T of DateTime
     *
     * @param T $first
     * @param T ...$others
     *
     * @return non-empty-list<T>
     */
    function chronologize(DateTime $first, DateTime ...$others): array
    {
        /** @var list<T> $chronology */
        $chronology = [$first, ...\array_values($others)];
        \usort($chronology, static function(DateTime $a, DateTime $b): int {
            return $a->compareTo($b)->value();
        });

        return $chronology;
    }
}

if (! \function_exists('\\Kronika\\timezone_utc')) {
    /**
     * Returns UTC time-zone.
     */
    function timezone_utc(): \DateTimeZone
    {
        static $utc = new \DateTimeZone('UTC');

        return $utc;
    }
}

if (! \function_exists('\\Kronika\\timezone_system')) {
    /**
     * Returns the system's time-zone.
     */
    function timezone_system(): \DateTimeZone
    {
        static $timezone = new \DateTimeZone(\date_default_timezone_get());
        if ($timezone->getName() !== \date_default_timezone_get()) {
            $timezone = new \DateTimeZone(\date_default_timezone_get());
        }

        return $timezone;
    }
}

if (! \function_exists('\\Kronika\\formatter')) {
    /**
     * Returns a global formatter.
     * Changes and returns a global formatter with a given one.
     */
    function formatter(?Formatter $asGlobal = null): Formatter
    {
        static $global = $asGlobal ?? Format\native();

        return $global = $asGlobal ?? $global;
    }
}
