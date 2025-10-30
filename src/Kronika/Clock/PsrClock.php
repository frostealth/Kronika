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

use Kronika\Clock as KronikaClock;
use Kronika\ZonedDateTime;

if (! \interface_exists('Psr\\Clock\\ClockInterface')) {
    throw new \ErrorException('"psr/clock" package is not installed');
}

/**
 * "PSR-20: Clock" implementation.
 *
 * ```
 * use Psr\Clock\ClockInterface;
 *
 * $foo = fn(ClockInterface $clock) => $clock->now();
 * $foo(new PsrClock(new SystemClock($timezone)));
 * ```
 */
final readonly class PsrClock implements \Psr\Clock\ClockInterface
{
    public function __construct(
        private KronikaClock $clock,
    ) {
    }

    #[\Override]
    public function now(): ZonedDateTime
    {
        return $this->clock->now();
    }
}
