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

namespace Kronika\Extension\Symfony\Clock;

use Kronika\Clock as KronikaClock;
use Kronika\Duration;
use Kronika\Instant;
use Kronika\ZonedDateTime;
use Symfony\Component\Clock\ClockInterface as SymfonyClock;

final readonly class KronikaClockAdapter implements SymfonyClock
{
    private \DateTimeZone $timezone;

    public function __construct(
        private KronikaClock $clock,
        ?\DateTimeZone $timezone = null,
    ) {
        $this->timezone = $timezone ?? $clock->now()->timezone();
    }

    #[\Override]
    public function now(): ZonedDateTime
    {
        return $this->clock->now()->shift($this->timezone);
    }

    #[\Override]
    public function sleep(float|int $seconds): void
    {
        $micro = 0;
        if ($seconds <= 0) {
            return;
        }
        if (\is_float($seconds)) {
            [$seconds, $micro] = \sscanf((string)$seconds, '%d.%6d');
        }

        $this->clock->sleep(Duration::of(seconds: $seconds));

        if ($micro === 0) {
            return;
        } elseif ($this->clock instanceof KronikaClock\SystemClock) {
            \usleep($micro);
        } elseif ($this->clock instanceof KronikaClock\MutableClock) {
            $micro = $this->clock->now()->microsecond() + $micro;
            $second = $this->clock->now()->second()->second() + (int)($micro / 1_000_000);
            $instant = Instant::of($second, micro: $micro % 1_000_000);
            $this->clock->freeze(ZonedDateTime::ofInstant($instant, $this->clock->now()->timezone()));
        }
    }

    #[\Override]
    public function withTimeZone(\DateTimeZone|string $timezone): static
    {
        $timezone = $timezone instanceof \DateTimeZone ? $timezone : new \DateTimeZone($timezone);

        return new self($this->clock, $timezone);
    }
}
