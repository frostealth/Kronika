<?php

declare(strict_types=1);

namespace Kronika;

/**
 * The number of seconds counted from epoch of
 * "1970-01-01 00:00:00" in local time excluding the timezone.
 *
 * This is not the unix timestamp due to the representation of local time without a timezone.
 */
final readonly class Instant
{
    private function __construct(
        private int $second,
    ){
    }

    public static function of(int $second): self
    {
        return new self($second);
    }

    public function atTimezone(\DateTimeZone $timezone): ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this, $timezone);
    }

    public function add(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return self::of($this->second() + $duration->inSeconds());
    }

    public function sub(Duration $duration): self
    {
        if ($duration->isZero()) {
            return $this;
        }

        return self::of($this->second() - $duration->inSeconds());
    }

    public function until(self $end): Duration
    {
        $start = $this->second();
        $end = $end->second();

        return $end > $start ? Duration::of(seconds: $end - $start) : Duration::zero();
    }

    public function diff(self $other): Duration  // @todo: or "between()"?
    {
        return Duration::of(seconds: \abs($other->second() - $this->second()));
    }

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
        return Comparison::compare($this->second, $other->second);
    }

    public function merge(self ...$others): self
    {
        return self::of(\array_reduce(
            $others,
            static fn(int $carry, self $other): int => $carry + $other->second(),
            $this->second(),
        ));
    }

    public function second(): int
    {
        return $this->second;
    }
}
