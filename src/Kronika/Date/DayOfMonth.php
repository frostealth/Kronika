<?php

declare(strict_types=1);

namespace Kronika\Date;

use Kronika\Comparison;
use Kronika\Date;

/**
 * @psalm-type DayOfMonthValue=int<1,31>
 * @implements DateUnit<DayOfMonthValue>
 */
final readonly class DayOfMonth implements DateUnit
{
    /** @use DateUnitTrait<DayOfMonthValue> */
    use DateUnitTrait;

    /** @psalm-param DayOfMonthValue $value */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : new self($value);
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

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%02d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['dayOfMonth' => (string) $this];
    }

    #[\Override]
    public function withinDate(Date $date): Date
    {
        return Date::of(
            year: $date->year(),
            month: $date->month(),
            day: $date->month()->adjustDay($this, $date->year()),
        );
    }

    #[\Override]
    protected static function minValue(): int
    {
        return 1;
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 31;
    }
}
