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

namespace Kronika\Time\Trait;

/**
 * @internal
 * @psalm-require-implements \Kronika\DateTime
 */
trait HasTime
{
    /**
     * Moves to the beginning of the current hour.
     *
     * @see \Kronika\Time::startOfHour()
     */
    final public function startOfHour(): static
    {
        return $this->with($this->time()->startOfHour());
    }

    /**
     * Moves to the end of the current hour.
     *
     * @see \Kronika\Time::endOfHour()
     */
    final public function endOfHour(): static
    {
        return $this->with($this->time()->endOfHour());
    }
}
