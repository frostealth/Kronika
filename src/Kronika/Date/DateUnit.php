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

namespace Kronika\Date;

use Kronika\Date;
use Kronika\Unit;

/**
 * Represents a unit of day (date).
 *
 * @template-covariant TDateUnit of int
 * @internal
 */
interface DateUnit extends Unit
{
    /**
     * Returns the number of this date unit.
     *
     * @return TDateUnit
     */
    public function number(): int;

    /** @internal {@see \Kronika\Date::with()} */
    public function _withinDate(Date $date): Date;
}
