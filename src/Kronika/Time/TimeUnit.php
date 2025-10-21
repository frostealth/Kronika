<?php

declare(strict_types=1);

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