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
 * Represents a mutable clock.
 * This is useful in tests.
 */
final class MutableClock implements Clock
{
    private readonly Clock $initial;
    private Clock $current;

    public function __construct(Clock $clock)
    {
        $this->initial = $clock;
        $this->current = $clock;
    }

    #[\Override]
    public function now(): ZonedDateTime
    {
        return $this->current->now();
    }

    #[\Override]
    public function sleep(Duration $duration): void
    {
        $this->current->sleep($duration);
    }

    /**
     * Freezes this clock time at the current moment or a given time.
     *
     * ```
     * $this->now();    // 2025-12-31 12:15:30.000055
     * $this->now();    // 2025-12-31 12:15:30.000115
     *
     * $this->freeze();
     * $this->now();    // 2025-12-31 12:15:30.000123
     * $this->now();    // 2025-12-31 12:15:30.000123
     *
     * $this->freeze(
     *     new \DateTime('2026-01-01 00:00:00.000000'),
     * );
     * $this->now();    // 2026-01-01 00:00:00.000000
     * $this->now();    // 2026-01-01 00:00:00.000000
     * ```
     */
    public function freeze(?\DateTimeInterface $at = null): void
    {
        $this->current = new FrozenClock($at ?? $this->current->now());
    }

    /**
     * Changes the inner clock to a given one.
     */
    public function changeClock(Clock $to): void
    {
        $this->current = $to;
    }

    /**
     * Resets the inner clock to the initial.
     *
     * ```
     * $this->freeze();
     * $this->now();    // 2025-12-31 12:15:30.000123
     * $this->now();    // 2025-12-31 12:15:30.000123
     *
     * $this->reset();
     * $this->now();    // 2025-12-31 12:15:30.000845
     * ```
     *
     * @see self::freeze()
     * @see self::changeClock()
     */
    public function reset(): void
    {
        $this->current = $this->initial;
    }
}
