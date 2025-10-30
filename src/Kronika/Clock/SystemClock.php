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

namespace Kronika\Clock;

use Kronika\Clock;
use Kronika\Duration;
use Kronika\ZonedDateTime;

/**
 * Represents a clock that returns the system time.
 *
 * ```
 * $clock = new SystemClock(new \DateTimeZone('+01:00'));
 *
 * $clock->now();  // 2025-12-31 12:15:30.999995 +01:00
 * sleep(10);
 * $clock->now();  // 2025-12-31 12:15:40.999999 +01:00
 * ```
 */
final readonly class SystemClock implements Clock
{
    public function __construct(
        private ?\DateTimeZone $timezone = null,
    ) {
    }

    #[\Override]
    public function now(): ZonedDateTime
    {
        return ZonedDateTime::ofDateTime(new \DateTime(timezone: $this->timezone));
    }

    #[\Override]
    public function sleep(Duration $duration): void
    {
        \sleep($duration->inSeconds());
    }
}
