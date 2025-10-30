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

/**
 * Represents a clock.
 */
interface Clock
{
    /**
     * Returns the current time with time-zone.
     *
     * ```
     * // 2025-12-31 12:15:00.000000 +01:00
     * $now = $clock->now();
     * ```
     */
    public function now(): ZonedDateTime;

    /**
     * Clock sleeps for a given duration.
     *
     * ```
     * $this->now();  // 2025-12-31 12:15:30 UTC
     * $this->sleep(Duration::of(minutes: 5));
     * $this->now();  // 2025-12-31 12:20:30 UTC
     * ```
     */
    public function sleep(Duration $duration): void;
}