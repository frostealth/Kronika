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

use Kronika\Duration;
use Kronika\Precision;
use Kronika\Time;
use Kronika\Unit;
use Kronika\Utils\Compared;

/**
 * Represents a unit of time.
 *
 * @template TTimeUnit of int
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
     * Checks if this time unit's value is equal to a given one.
     *
     * @param TTimeUnit $value
     */
    public function is(int $value): bool;

    /** @internal {@see Time::compareTo()} */
    public function _compareInTime(Time $that, Precision $precision): Compared;

    /** @internal {@see Time::until()} */
    public function _untilInTime(Time $start): Duration;

    /** @internal {@see Time::with()} */
    public function _withinTime(Time $time): Time;
}