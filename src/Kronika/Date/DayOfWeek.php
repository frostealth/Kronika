<?php

declare(strict_types=1);

namespace Kronika\Date;

use Kronika\Comparison;
use Kronika\Date;
use Kronika\Duration;
use Kronika\LocalDateTime;

/**
 * @psalm-type DayOfWeekValue=value-of<DayOfWeek>
 * @psalm-type DayOfWeekNativeValue=int<0,6>
 * @psalm-type DayOfWeekName='Monday'|'Tuesday'|'Wednesday'|'Thursday'|'Friday'|'Saturday'|'Sunday'
 * @implements DateUnit<DayOfWeekValue>
 */
enum DayOfWeek: int implements DateUnit
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    /** @psalm-param DayOfWeekValue|DayOfWeekName|DayOfWeekNativeValue $value */
    public static function of(string|int|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }
        if (\is_string($value)) {
            return self::{\ucfirst($value)};
        }
        if (0 === $value) {
            return self::Sunday;
        }

        return self::from($value);
    }

    #[\Override]
    public function is(self|int $number): bool
    {
        $number = $number instanceof self ? $number->value : $number;

        return $this->value === $number;
    }

    /**
     * @template iso of bool
     *
     * @param iso $iso
     *
     * @psalm-return iso is not false ? DayOfWeekValue : DayOfWeekNativeValue
     */
    #[\Override]
    public function number(bool $iso = true): int
    {
        if (! $iso && self::Sunday === $this) {
            return 0;
        }

        return $this->value;
    }

    /** @psalm-return DayOfWeekName */
    public function name(): string
    {
        return $this->name;
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    public function compareTo(self $other): Comparison
    {
        return Comparison::compare($this->value, $other->value);
    }

    public function next(): self
    {
        if ($this === self::Sunday) {
            return self::Monday;
        }

        return self::of($this->number() + 1);
    }

    public function previous(): self
    {
        if ($this === self::Monday) {
            return self::Sunday;
        }

        return self::of($this->number() - 1);
    }

    private function diff(self $other): Duration
    {
        return Duration::of(days: \abs($this->number() - $other->number()));
    }

    #[\Override]
    public function withinDate(Date $date): Date
    {
        if ($this->isBefore($date->dayOfWeek())) {
            return $date->sub($this->diff($date->dayOfWeek()));
        }

        return $date->add($this->diff($date->dayOfWeek()));
    }

    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinDate($dateTime->date()));
    }
}
