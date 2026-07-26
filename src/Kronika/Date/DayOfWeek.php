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

namespace Kronika\Date;

use Kronika\Date;
use Kronika\Duration;
use Kronika\OverflowMode;
use Kronika\Utils\Compared;

/**
 * Represents a day of week.
 *
 * @psalm-type TDayOfWeek=value-of<DayOfWeek>
 * @psalm-type TDayOfWeekNative=int<0,6>
 * @psalm-type TDayOfWeekName='Monday'|'Tuesday'|'Wednesday'|'Thursday'|'Friday'|'Saturday'|'Sunday'
 * @implements DateUnit<TDayOfWeek>
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

    use Trait\DateUnit;

    /**
     * Obtains an instance of `DayOfWeek` from a number or name.
     *
     * ```
     * // Monday
     * $dayOfWeek = DayOfWeek::Monday;
     * $dayOfWeek = DayOfWeek::of(1);
     * $dayOfWeek = DayOfWeek::of('monday');
     * ```
     * ```
     * // Sunday
     * $dayOfWeek = DayOfWeek::Sunday;
     * $dayOfWeek = DayOfWeek::of(7);
     * $dayOfWeek = DayOfWeek::of(0);
     * ```
     *
     * @throws Exception\InvalidDayOfWeek
     */
    public static function of(int|string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return \is_int($value) ? self::ofValue($value) : self::ofName($value);
    }

    /**
     * Returns the number of this day of week.
     *
     * ```
     * DayOfWeek::Sunday->number();           // 7
     * DayOfWeek::Sunday->number(iso: false); // 0
     * ```
     *
     * @return ($iso is true ? TDayOfWeek : TDayOfWeekNative)
     */
    #[\Override]
    public function number(bool $iso = true): int
    {
        if (! $iso && self::Sunday === $this) {
            return 0;
        }

        return $this->value;
    }

    /**
     * Returns the name of this day of week.
     *
     * ```
     * DayOfWeek::Wednesday->name();  // Wednesday
     * ```
     *
     * @return TDayOfWeekName
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the next day of week.
     *
     * ```
     * DayOfWeek::Monday->next();  // Tuesday
     * ```
     */
    public function next(OverflowMode $mode = OverflowMode::Clamp): self
    {
        if ($this->is(self::Sunday)) {
            return match ($mode) {
                OverflowMode::Roll => self::Monday,
                OverflowMode::Strict => throw new \OutOfBoundsException(),
                OverflowMode::Clamp => $this,
            };
        }

        return self::of($this->number() + 1);
    }

    /**
     * Returns the previous day of week.
     *
     * ```
     * DayOfWeek::Monday->previous(OverflowMode::Clamp); // Monday
     * DayOfWeek::Monday->previous(OverflowMode::Roll);  // Sunday
     * ```
     */
    public function previous(OverflowMode $mode = OverflowMode::Clamp): self
    {
        if ($this->is(self::Monday)) {
            return match ($mode) {
                OverflowMode::Roll => self::Sunday,
                OverflowMode::Strict => throw new \OutOfBoundsException(),
                OverflowMode::Clamp => $this,
            };
        }

        return self::of($this->number() - 1);
    }

    /**
     * Checks if this day of week is weekday (working day).
     *
     * @see self::isWeekend()
     * @see self::is()
     */
    public function isWeekday(): bool
    {
        return ! $this->isWeekend();
    }

    /**
     * Checks if this day of week is weekend.
     *
     * @see self::isWeekday()
     * @see self::is()
     */
    public function isWeekend(): bool
    {
        return match ($this) {
            self::Saturday,
            self::Sunday => true,
            default => false,
        };
    }

    /**
     * Checks if this day of week is equal to another one.
     *
     * ```
     * // Wednesday
     * $this->is(DayOfWeek::Wednesday);  // true
     * $this->is(DayOfWeek::Monday);     // false
     * $this->is(DayOfWeek::Friday);     // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this day of week is not equal to another one.
     *
     * ```
     * // Wednesday
     * $this->isNot(DayOfWeek::Wednesday);  // false
     * $this->isNot(DayOfWeek::Monday);     // true
     * $this->isNot(DayOfWeek::Friday);     // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this day of week is before another one.
     *
     * ```
     * // Wednesday
     * $this->isBefore(DayOfWeek::Wednesday);  // false
     * $this->isBefore(DayOfWeek::Monday);     // false
     * $this->isBefore(DayOfWeek::Friday);     // true
     * ```
     */
    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this day of week is before or equal to another one.
     *
     * ```
     * // Wednesday
     * $this->isBeforeOrEqualTo(DayOfWeek::Wednesday);  // true
     * $this->isBeforeOrEqualTo(DayOfWeek::Monday);     // false
     * $this->isBeforeOrEqualTo(DayOfWeek::Friday);     // true
     * ```
     */
    public function isBeforeOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this day of week is after or equal to another one.
     *
     * ```
     * // Wednesday
     * $this->isAfterOrEqualTo(DayOfWeek::Wednesday);  // true
     * $this->isAfterOrEqualTo(DayOfWeek::Monday);     // true
     * $this->isAfterOrEqualTo(DayOfWeek::Friday);     // false
     * ```
     */
    public function isAfterOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this day of week is after another one.
     *
     * ```
     * // Wednesday
     * $this->isAfter(DayOfWeek::Wednesday);  // false
     * $this->isAfter(DayOfWeek::Monday);     // true
     * $this->isAfter(DayOfWeek::Friday);     // false
     * ```
     */
    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this day of week to another one.
     *
     * ```
     * // Wednesday vs Friday
     * $this->compareTo($other)->less();    // true
     * $this->compareTo($other)->equal();   // false
     * $this->compareTo($other)->greater(); // false
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::of($this->value <=> $other->value);
    }

    /** @throws Exception\InvalidDayOfWeek */
    private static function ofName(string $name): self
    {
        try {
            return self::{\ucfirst($name)};
        } catch (\Throwable $e) {
            throw new Exception\InvalidDayOfWeek("Invalid day of week name [$name]", previous: $e);
        }
    }

    /** @throws Exception\InvalidDayOfWeek */
    private static function ofValue(int $value): self
    {
        if (0 === $value) {
            return self::Sunday;
        }

        try {
            return self::from($value);
        } catch (\ValueError $e) {
            throw new Exception\InvalidDayOfWeek("Day of week must be between 0 and 7, got [$value]", previous: $e);
        }
    }

    /** @internal {@see \Kronika\Date::with()} */
    #[\Override]
    public function _withinDate(Date $date, OverflowMode $mode): Date
    {
        $diff = Duration::of(days: \abs($this->number() - $date->dayOfWeek()->number()));

        return $this->isBefore($date->dayOfWeek()) ? $date->sub($diff) : $date->add($diff);
    }
}
