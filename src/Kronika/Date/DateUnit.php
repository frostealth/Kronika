<?php

declare(strict_types=1);

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
