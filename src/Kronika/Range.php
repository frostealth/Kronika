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
 * Represents a range of date-time, date, time.
 *
 * @template-covariant TUnit of object
 */
interface Range
{
    /**
     * Returns the start of this range, inclusive.
     *
     * @return TUnit
     */
    public function from(): object;

    /**
     * Returns the end of this range, exclusive.
     *
     * @return TUnit
     */
    public function to(): object;

    /**
     * Checks if this range's boundaries coincide with the same point.
     */
    public function isZero(): bool;

    /**
     * Returns the duration of this range.
     */
    public function duration(): Duration;

    /**
     * Splits this range into smaller ranges according to a given step.
     *
     * @return iterable<static>
     */
    public function split(Duration $step): iterable;

    /**
     * Returns all items of this range according to a given step.
     *
     * @return iterable<TUnit>
     */
    public function each(Duration $step): iterable;
}
