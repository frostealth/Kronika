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
 * Represents a clock that doesn't move forward on its own.
 *
 * ```
 * $time = ZonedDateTime::of(
 *     date: Date::of(2025, 12, 31),
 *     time: Time::midday(),
 *     timezone: new \DateTimeZone('+01:00'),
 * );
 * $clock = new FrozenClock($time);
 *
 * $clock->now()->is($time);  // true
 * sleep(10);
 * $clock->now()->is($time);  // true
 *
 * $clock->sleep(Duration::of(seconds: 5));
 * $clock->now()->is($time);  // false
 * ```
 */
final class FrozenClock implements Clock
{
    private ZonedDateTime $time;

    public function __construct(?\DateTimeInterface $time = null)
    {
        $this->time = ZonedDateTime::ofDateTime($time ?? new \DateTimeImmutable('now'));
    }

    #[\Override]
    public function now(): ZonedDateTime
    {
        return $this->time;
    }

    #[\Override]
    public function sleep(Duration $duration): void
    {
        $this->time = $this->time->add($duration);
    }
}
