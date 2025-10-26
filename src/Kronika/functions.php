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

if (! \function_exists('\\Kronika\\now')) {
    /**
     * Obtains an instance of ZonedDateTime from the current time and a given or system default time-zone.
     *
     * @example
     * ```
     * // 2025-12-31 12:15:30.000155 UTC
     * $datetime = now();
     *
     * // 2025-12-31 13:15:30.000155 +01:00
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
        return ZonedDateTime::ofDateTime(new \DateTimeImmutable(timezone: $timezone));
    }
}
