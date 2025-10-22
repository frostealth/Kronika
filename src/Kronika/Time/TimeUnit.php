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
 * @template TTimeUnit of int
 * @internal
 */
interface TimeUnit extends Unit
{
    public function isZero(): bool;

    public function isLast(): bool;

    /** @param TTimeUnit $value */
    public function is(int $value): bool;

    /** @internal */
    public function withinTime(Time $time): Time;
}