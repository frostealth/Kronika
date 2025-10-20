<?php

declare(strict_types=1);

namespace Kronika\Time;

use Kronika\Comparison;
use Kronika\Time;

/**
 * @psalm-type MinuteValue=int<0,59>
 * @extends TimeUnit<MinuteValue>
 */
final readonly class Minute extends TimeUnit
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
        return Time::of($time->hour(), $this, $time->second());
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 59;
    }
}
