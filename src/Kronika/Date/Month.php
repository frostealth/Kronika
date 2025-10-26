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
use Kronika\LocalDateTime;
use Kronika\Utils\Compared;

/**
 * @psalm-type TMonth=value-of<Month>
 * @psalm-type TMonthName='January'|'February'|'March'|'April'|'May'|'June'|'July'|'August'|'September'|'October'|'November'|'December'
 * @implements DateUnit<TMonth>
 */
enum Month: int implements DateUnit
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    /** @psalm-param TMonth $value */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::from($value);
    }

    public function length(Year $year): int
    {
        return match ($this) {
            self::February => $year->isLeap() ? 29 : 28,
            self::April,
            self::June,
            self::September,
            self::November => 30,
            default => 31,
        };
    }

    public function containsDay(DayOfMonth $day, Year $year): bool
    {
        return $day->number() <= $this->length($year);
    }

    #[\Override]
    public function is(int|self $number): bool
    {
        $number = $number instanceof self ? $number->value : $number;

        return $number === $this->value;
    }

    #[\Override]
    public function number(): int
    {
        return $this->value;
    }

    /** @psalm-return TMonthName */
    public function name(): string
    {
        return $this->name;
    }

    public function lastDay(Year $year): DayOfMonth
    {
        return DayOfMonth::of($this->length($year));
    }

    public function next(): self
    {
        if ($this === self::December) {
            return self::January;
        }

        return self::of($this->number() + 1);
    }

    public function previous(): self
    {
        if ($this === self::January) {
            return self::December;
        }

        return self::of($this->number() - 1);
    }

    public function duration(Year $year): Duration
    {
        return Duration::of(days: $this->length($year));
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    public function isBeforeOrEqual(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    public function isNotEqualTo(self $other): bool
    {
        return ! $this->isEqualTo($other);
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    public function isAfterOrEqual(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    public function compareTo(self $other): Compared
    {
        return Compared::compare($this->number(), $other->number());
    }

    /** @internal */
    public function adjustDay(DayOfMonth $day, Year $year): DayOfMonth
    {
        if (! $this->containsDay($day, $year)) {
            return $this->lastDay($year);
        }

        return $day;
    }

    /** @internal */
    #[\Override]
    public function withinDate(Date $date): Date
    {
        return Date::of(
            year: $date->year(),
            month: $this,
            day: $this->adjustDay($date->day(), $date->year()),
        );
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinDate($dateTime->date()));
    }
}
