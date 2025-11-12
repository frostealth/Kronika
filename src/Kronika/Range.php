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
 * Represents a range of date-time, date or time.
 *
 * @template-covariant TUnit of DateTime|Date|Time
 */
interface Range
{
    /**
     * Returns the start of this range, inclusive.
     *
     * @return TUnit
     */
    public function since(): object;

    /**
     * Returns the end of this range, exclusive.
     *
     * @return TUnit
     */
    public function till(): object;

    /**
     * Checks if this range's boundaries coincide with the same point.
     */
    public function isZero(): bool;

    /**
     * Returns the duration of this range.
     */
    public function duration(): Duration;

    /**
     * Returns all items of this range with a given step.
     *
     * @return \Traversable<int, TUnit>
     */
    public function each(Duration $step): \Traversable;
}
