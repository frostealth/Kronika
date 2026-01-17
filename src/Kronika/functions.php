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
     *
     * @see \Kronika\Clock
     * @see \Kronika\clock()
     */
    function now(?\DateTimeZone $timezone = null): ZonedDateTime
    {
        return \is_null($timezone) ? clock()->now() : clock()->now()->shift($timezone);
    }
}

if (! \function_exists('\\Kronika\\clock')) {
    /**
     * Global clock.
     *
     * @see \Kronika\Clock
     * @see \Kronika\now()
     */
    function clock(?Clock $asGlobal = null): Clock
    {
        static $global = $asGlobal ?? new Clock\SystemClock();

        return $global = $asGlobal ?? $global;
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
