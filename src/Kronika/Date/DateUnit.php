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
 * @template TDateUnit of int
 * @internal
 */
interface DateUnit extends Unit
{
    /** @return TDateUnit */
    public function number(): int;

    /** @param TDateUnit $number */
    public function is(int $number): bool;

    /** @internal */
    public function withinDate(Date $date): Date;
}
