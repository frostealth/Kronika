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

namespace Kronika\Time;

use Kronika\Time;
use Kronika\Unit;

/**
 * Represents a unit of time.
 *
 * @template-covariant TTimeUnit of numeric
 * @internal
 */
interface TimeUnit extends Unit
{
    /**
     * Checks if this time unit represents zero.
     */
    public function isZero(): bool;

    /**
     * Checks if this time unit represents the last value of the unit.
     */
    public function isLast(): bool;

    /**
     * Returns the value of this time unit.
     *
     * @return TTimeUnit
     */
    public function value(): float|int|string;

    /** @internal {@see \Kronika\Time::with()} */
    public function _withinTime(Time $time): Time;
}
