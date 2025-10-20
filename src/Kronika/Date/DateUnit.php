<?php

declare(strict_types=1);

namespace Kronika\Date;

use Kronika\Date;
use Kronika\Unit;

/**
 * @template DateUnitValue of int
 * @internal
 */
interface DateUnit extends Unit
{
    /** @return DateUnitValue */
    public function number(): int;

    /** @param DateUnitValue $number */
    public function is(int $number): bool;

    /** @internal */
    public function withinDate(Date $date): Date;
}
