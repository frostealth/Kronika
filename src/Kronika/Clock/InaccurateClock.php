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
use Kronika\Precision;
use Kronika\ZonedDateTime;

/**
 * Represents a clock that resets
 * time units to "0" according to a given precision.
 *
 * ```
 * $system = new SystemClock(new \DateTimeZone('+01:00'));
 * $clock = new InaccurateClock($system, Precision::Second);
 *
 * $system->now(); // 2025-12-31 12:15:30.999000 +01:00
 * $clock->now();  // 2025-12-31 12:15:30.000000 +01:00
 * sleep(10);
 * $system->now(); // 2025-12-31 12:15:40.999000 +01:00
 * $clock->now();  // 2025-12-31 12:15:40.000000 +01:00
 * ```
 */
final readonly class InaccurateClock implements Clock
{
    public function __construct(
        private Clock $clock,
        private Precision $precision,
    ) {
    }

    #[\Override]
    public function now(): ZonedDateTime
    {
        return match($this->precision) {
            Precision::Micro => $this->clock->now(),
            Precision::Second => $this->clock->now()->resetMicro(),
            Precision::Minute => $this->clock->now()->resetSecond(),
        };
    }

    #[\Override]
    public function sleep(Duration $duration): void
    {
        $this->clock->sleep($duration);
    }
}
