<?php

declare(strict_types=1);

namespace Kronika\Date;

use Kronika\Comparison;
use Kronika\Date;
use Kronika\Duration;

/**
 * @psalm-type YearValue=int<0,9999>
 * @implements DateUnit<YearValue>
 */
final readonly class Year implements DateUnit
{
    /** @use DateUnitTrait<YearValue> */
    use DateUnitTrait;

    /** @psalm-param YearValue $value */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : new self($value);
    }

    /** @return non-negative-int */
    public function length(): int
    {
        return $this->isLeap() ? 366 : 365;
    }

    public function isLeap(): bool
    {
        if (0 !== $this->number() % 4) {
            return false;
        }
        if (0 !== $this->number() % 100) {
            return false;
        }
        if (0 !== $this->number() % 400) {
            return false;
        }

        return true;
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
        return Comparison::compare($this->number(), $other->number());
    }

    public function next(): self
    {
        return new self($this->number() + 1);
    }

    public function previous(): self
    {
        return new self($this->number() - 1);
    }

    public function duration(): Duration
    {
        return Duration::of(days: $this->length());
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%04d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['year' => (string) $this];
    }

    #[\Override]
    public function withinDate(Date $date): Date
    {
        return Date::of(
            year: $this,
            month: $date->month(),
            day: $date->month()->adjustDay($date->day(), $this),
        );
    }

    #[\Override]
    protected static function minValue(): int
    {
        return 0;
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 9999;
    }
}
