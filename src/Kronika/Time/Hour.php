<?php

declare(strict_types=1);

namespace Kronika\Time;

use Kronika\Comparison;
use Kronika\Time;

/**
 * @psalm-type HourValue=int<0,23>
 * @extends TimeUnit<HourValue>
 */
final readonly class Hour extends TimeUnit
{
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    public function compareTo(self $other): Comparison
    {
        return Comparison::compare($this->value(), $other->value());
    }

    #[\Override]
    public function withinTime(Time $time): Time
    {
        return Time::of($this, $time->minute(), $time->second());
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 23;
    }
}
