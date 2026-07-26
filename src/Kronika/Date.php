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

namespace Kronika;

use DateTimeInterface as Native;
use Kronika\Date\DateUnit;
use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\DayOfYear;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Format\Date\Formatted;
use Kronika\Format\Date\Formatter;
use Kronika\Utils\Compared;
use Kronika\Utils\RefTrait;
use Kronika\Utils\RescueTrait;

/**
 * Represents a date.
 *
 * @method static static|null tryOf(mixed $year, mixed $month, mixed $day)
 * @method static static|null tryOfFormat(string $format, ?string $date, ?Formatter $formatter = null)
 * @method static static|null tryParse(?string $date)
 *
 * @psalm-import-type TMonth from \Kronika\Date\Month
 */
final readonly class Date implements Unit
{
    use RefTrait;
    use RescueTrait;

    /**
     * Obtains an instance of `Date` from a year, month and day of the month.
     *
     * ```
     * // 2025-12-31
     * $date = Date::of(year: 2025, month: 12, day: 31);
     * $date = Date::of(Year::of(2025), Month::December, DayOfMonth::of(31));
     * ```
     *
     * @throws Exception\InvalidDate
     */
    public static function of(Year|int $year, Month|int $month, DayOfMonth|int $day): self
    {
        return self::ref(year: Year::of($year), month: Month::of($month)->number(), day: DayOfMonth::of($day));
    }

    /**
     * Obtains an instance of `Date` from a date-time.
     */
    public static function ofDateTime(DateTime|Native $datetime): self
    {
        if ($datetime instanceof DateTime) {
            return $datetime->date();
        }

        return self::map($datetime, static function (Native $datetime): self {
            return self::of(...\sscanf($datetime->format('x-m-d'), '%d-%d-%d'));
        }, when: static fn(Native $datetime): bool => $datetime instanceof \DateTimeImmutable);
    }

    /**
     * Obtains an instance of `Date` from a given format and date string.
     *
     * @param non-empty-string $format
     * @param non-empty-string $date
     *
     * @throws Exception\FormatError
     */
    public static function ofFormat(string $format, string $date, ?Formatter $formatter = null): self
    {
        $parsed = ($formatter ?? formatter())->parse(new Formatted($format, $date));

        return self::of($parsed->year(), $parsed->month(), $parsed->day());
    }

    /**
     * Obtains an instance of `Date` from a given date string.
     *
     * ```
     * $date = Date::parse('2025-12-31');
     * ```
     *
     * @param non-empty-string $date
     *
     * @throws Exception\MalformedString
     */
    public static function parse(string $date): self
    {
        if ($date === '' || \in_array(\strtolower($date), ['now', 'today'], strict: true)) {
            throw new Exception\MalformedString\DateMalformedString('Invalid date string');
        }

        try {
            return self::ofDateTime(new \DateTimeImmutable($date));
        } catch (\DateMalformedStringException $e) {
            throw Exception\MalformedString\DateMalformedString::wrap($e);
        }
    }

    /**
     * @param TMonth $month
     *
     * @throws Exception\InvalidDate
     */
    private function __construct(
        private Year $year,
        // enums are not comparable using `<`, `>` and `<=>`
        private int $month,
        private DayOfMonth $day,
    ) {
        if (! $this->month()->containsDay($day, $year)) {
            throw new Exception\InvalidDate(\sprintf('Invalid date [%s]', $this));
        }
    }

    /**
     * Returns an instance of `Year `from this date.
     */
    public function year(): Year
    {
        return $this->year;
    }

    /**
     * Returns an instance of `Month` from this date.
     */
    public function month(): Month
    {
        return Month::of($this->month);
    }

    /**
     * Returns an instance of `DayOfMonth` from this date.
     */
    public function day(): DayOfMonth
    {
        return $this->day;
    }

    /**
     * Returns an instance of `DayOfWeek` from this date.
     */
    public function dayOfWeek(): DayOfWeek
    {
        return $this->remember(static fn(self $date): DayOfWeek => DayOfWeek::of(
            (int)\gmdate('N', $date->instant()->second()),
        ), key: DayOfWeek::class);
    }

    /**
     * Returns an instance of `DayOfYear` from this date.
     */
    public function dayOfYear(): DayOfYear
    {
        return $this->remember(static fn(self $date): DayOfYear => DayOfYear::of(
            $date->startOfYear()->until($date)->add(Duration::ofDay())->inDays(),
        ), key: DayOfYear::class);
    }

    /**
     * Returns an instance of `Date` with a given date unit.
     *
     * If the day of the month of the resulting date is greater than
     * the length of the month, then the last day of the month will be set.
     *
     * ```
     * // 2025-11-29
     * $this->with(Year::of(1990));         // 1990-11-29
     * $this->with($this->year()->next());  // 2026-11-29
     * $this->with(Month::January);         // 2025-01-29
     * $this->with(Month::February);        // 2025-02-28
     * $this->with(DayOfMonth::of(15));     // 2025-11-15
     * $this->with(DayOfMonth::of(31));     // 2025-11-30
     * $this->with(DayOfWeek::Monday);      // 2025-11-24
     * ```
     * ```
     * // 2025-11-29 vs DayOfMonth::of(31)
     * $this->with($day, rolling: false); // 2025-11-30
     * $this->with($day, rolling: true);  // 2025-12-01
     * ```
     *
     * @see \Kronika\Date\Year – change only year
     * @see \Kronika\Date\Month – change only month
     * @see \Kronika\Date\DayOfMonth – change only day of month
     * @see \Kronika\Date\DayOfWeek – change/shift only day of week
     * @see \Kronika\Date\DayOfYear - change/shift only day of year
     */
    public function with(DateUnit $unit, bool $rolling = false): self
    {
        return $unit->_withinDate($this, $rolling);
    }

    /**
     * Combines this date with a given time to create an instance of `LocalDateTime`.
     *
     * ```
     * // 2025-12-31
     * $this->at(Time::of(12, 15, 30));  // 2025-12-31 12:15:30
     * ```
     */
    public function at(Time $time): LocalDateTime
    {
        return LocalDateTime::of(date: $this, time: $time);
    }

    /**
     * Combines this date with the time of midnight to create an instance of `LocalDateTime`.
     *
     * ```
     * // 2025-12-31
     * $this->atMidnight();  // 2025-12-31 00:00:00.000000
     * ```
     *
     * @see \Kronika\Time::midnight()
     * @see self::at()
     */
    public function atMidnight(): LocalDateTime
    {
        return $this->at(Time::midnight());
    }

    /**
     * Combines this date with the time of midday/noon to create an instance of `LocalDateTime`.
     *
     * ```
     * // 2025-12-31
     * $this->atMidday();  // 2025-12-31 12:00:00.000000
     * ```
     *
     * @see \Kronika\Time::midday()
     * @see self::at()
     */
    public function atMidday(): LocalDateTime
    {
        return $this->at(Time::midday());
    }

    /**
     * Combines this date with the time of the end of the day to create an instance of `LocalDateTime`.
     *
     * ```
     * // 2025-12-31
     * $this->atEndOfDay();  // 2025-12-31 23:59:59.999999
     * ```
     *
     * @see \Kronika\Time::endOfDay()
     * @see self::at()
     */
    public function atEndOfDay(): LocalDateTime
    {
        return $this->at(Time::endOfDay());
    }

    /**
     * Adds an amount of days, hours, minutes and seconds to this date.
     *
     * ```
     * // 2025-12-31 + 2 days
     * $this->add(Duration::of(days: 2));  // 2026-01-12
     *
     * // 2025-12-31 + 24 hours
     * $this->add(Duration::of(hours: 24));  // 2026-01-01
     * ```
     */
    public function add(Duration $duration): self
    {
        return self::fromInstant($this->instant()->add($duration->roundToDays()));
    }

    /**
     * Subtracts an amount of days from this date.
     *
     * ```
     * // 2025-12-31 - 2 days
     * $this->sub(Duration::of(days: 2));  // 2025-12-29
     *
     * // 2025-12-31 - 24 hours
     * $this->sub(Duration::of(hours: 24));  // 2025-12-30
     * ```
     */
    public function sub(Duration $duration): self
    {
        return self::fromInstant($this->instant()->sub($duration->roundToDays()));
    }

    /**
     * Calculates the duration from this date or its unit to another one.
     *
     * ```
     * // 2025-12-31 vs 2026-01-02
     * $duration = $this->until($other);
     * $duration->days();   // 2
     *
     * // 2026-01-02 vs 2025-12-31
     * $duration = $this->until($other);
     * $duration->days();   // 0
     *
     * // 2026-01-02 vs Month::of(2)
     * $duration = $this->until($other);
     * $duration->days();   // 31
     * ```
     *
     * @see \Kronika\Date\Year
     * @see \Kronika\Date\Month
     * @see \Kronika\Date\DayOfMonth
     * @see \Kronika\Date\DayOfWeek
     * @see \Kronika\Date\DayOfYear
     */
    public function until(self|DateUnit $end): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant());
    }

    /**
     * Calculates the duration between this date or its unit and another one.
     *
     * ```
     * // 2025-12-31 vs 2026-01-02
     * $duration = $this->difference($other);
     * $duration->days();   // 2
     *
     * // 2026-01-02 vs 2025-12-31
     * $duration = $this->difference($other);
     * $duration->days();   // 2
     * ```
     *
     * @see \Kronika\Date\Year
     * @see \Kronika\Date\Month
     * @see \Kronika\Date\DayOfMonth
     * @see \Kronika\Date\DayOfWeek
     * @see \Kronika\Date\DayOfYear
     */
    public function difference(self|DateUnit $other): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant());
    }

    /**
     * Moves backward to the previous year.
     *
     * ```
     * // 2025-02-28
     * $this->previousYear();  // 2024-02-28
     * ```
     */
    public function previousYear(): self
    {
        return $this->with($this->year()->previous());
    }

    /**
     * Moves forward to the next year.
     *
     * ```
     * // 2025-12-15
     * $this->nextYear();  // 2026-12-15
     * ```
     */
    public function nextYear(): self
    {
        return $this->with($this->year()->next());
    }

    /**
     * Moves to the first day of the current year.
     *
     * ```
     * // 2025-07-15
     * $this->startOfYear();  // 2025-01-01
     * ```
     *
     * @see self::isStartOfYear()
     */
    public function startOfYear(): self
    {
        return self::of($this->year(), month: Month::January, day: DayOfMonth::first());
    }

    /**
     * Moves to the last day of the current year.
     *
     * ```
     * // 2025-07-15
     * $this->endOfYear();  // 2025-12-31
     * ```
     *
     * @see self::isEndOfYear()
     */
    public function endOfYear(): self
    {
        return self::of($this->year(), month: Month::December, day: 31);
    }

    /**
     * Moves backward to the previous month.
     *
     * ```
     * // 2025-03-31
     * $this->previousMonth();  // 2025-02-28
     * ```
     * ```
     * // 2025-01-30
     * $this->previousMonth();  // 2024-12-30
     * ```
     */
    public function previousMonth(): self
    {
        if ($this->month()->is(Month::January)) {
            return self::of($this->year()->previous(), month: Month::December, day: $this->day());
        }

        return $this->with($this->month()->previous());
    }

    /**
     * Moves forward to the next month.
     *
     * ```
     * // 2025-01-31
     * $this->nextMonth();  // 2025-02-28
     * ```
     * ```
     * // 2025-12-30
     * $this->nextMonth();  // 2026-01-30
     * ```
     */
    public function nextMonth(): self
    {
        if ($this->month()->is(Month::December)) {
            return self::of($this->year()->next(), month: Month::January, day: $this->day());
        }

        return $this->with($this->month()->next());
    }

    /**
     * Moves to the first day of the current month.
     *
     * ```
     * // 2025-12-31
     * $this->startOfMonth();  // 2025-12-01
     * ```
     *
     * @see self::isStartOfMonth()
     */
    public function startOfMonth(): self
    {
        return $this->with(DayOfMonth::first());
    }

    /**
     * Moves to the last day of the current month.
     *
     * ```
     * // 2025-02-01
     * $this->endOfMonth();  // 2025-02-28
     *
     * // 2024-02-01 – leap year
     * $this->endOfMonth();  // 2025-02-29
     * ```
     *
     * @see self::isEndOfMonth()
     */
    public function endOfMonth(): self
    {
        return $this->with($this->month()->lastDay($this->year()));
    }

    /**
     * Moves backward to the previous week.
     *
     * ```
     * // 2026-01-07, Wednesday
     * $this->previousWeek();  // 2025-12-31, Wednesday
     * ```
     */
    public function previousWeek(): self
    {
        return $this->sub(Duration::ofWeek());
    }

    /**
     * Moves forward to the next week.
     *
     * ```
     * // 2025-12-30, Tuesday
     * $this->nextWeek();  // 2026-01-06, Tuesday
     * ```
     */
    public function nextWeek(): self
    {
        return $this->add(Duration::ofWeek());
    }

    /**
     * Moves backward to the previous weekday.
     *
     * ```
     * // 2025-12-29, Monday
     * $this->previousWeekday();  // 2025-12-26, Friday
     * ```
     */
    public function previousWeekday(): self
    {
        return match ($this->dayOfWeek()) {
            DayOfWeek::Monday => $this->previousWeek()->with(DayOfWeek::Friday),
            DayOfWeek::Saturday,
            DayOfWeek::Sunday => $this->with(DayOfWeek::Friday),
            default => $this->previousDay(),
        };
    }

    /**
     * Moves forward to the next weekday.
     *
     * ```
     * // 2025-12-26, Friday
     * $this->nextWeekday();  // 2025-12-29, Monday
     * ```
     */
    public function nextWeekday(): self
    {
        if ($this->dayOfWeek()->isBefore(DayOfWeek::Friday)) {
            return $this->nextDay();
        }

        return $this->nextWeek()->with(DayOfWeek::Monday);
    }

    /**
     * Moves backward to the previous weekend day.
     *
     * ```
     * // 2025-12-27, Saturday
     * $this->previousWeekendDay();  // 2025-12-21, Sunday
     * ```
     */
    public function previousWeekendDay(): self
    {
        return match ($this->dayOfWeek()) {
            DayOfWeek::Monday,
            DayOfWeek::Sunday => $this->previousDay(),
            default => $this->previousWeek()->with(DayOfWeek::Sunday),
        };
    }
    /**
     * Moves forward to the next weekend day.
     *
     * ```
     * // 2025-12-21, Sunday
     * $this->nextWeekendDay();  // 2025-12-27, Saturday
     * ```
     */
    public function nextWeekendDay(): self
    {
        return match ($this->dayOfWeek()) {
            DayOfWeek::Saturday => $this->nextDay(),
            DayOfWeek::Sunday => $this->nextWeek()->with(DayOfWeek::Saturday),
            default => $this->with(DayOfWeek::Saturday),
        };
    }

    /**
     * Moves backward to the previous day.
     *
     * ```
     * // 2025-03-01
     * $this->previousDay();  // 2025-02-28
     * ```
     */
    public function previousDay(): self
    {
        return $this->sub(Duration::ofDay());
    }

    /**
     * Moves forward to the next day.
     *
     * ```
     * // 2025-12-31
     * $this->nextDay();  // 2026-01-01
     * ```
     */
    public function nextDay(): self
    {
        return $this->add(Duration::ofDay());
    }

    /**
     * Checks if the day of this date is the first day of the year.
     *
     * @see self::startOfYear()
     */
    public function isStartOfYear(): bool
    {
        return $this->is($this->startOfYear());
    }

    /**
     * Check if the day of this date is the last day of the year.
     *
     * @see self::endOfYear()
     */
    public function isEndOfYear(): bool
    {
        return $this->is($this->endOfYear());
    }

    /**
     * Checks if the day of the month of this date is the first in the month.
     *
     * ```
     * // 2025-12-01
     * $this->isStartOfMonth();  // true
     *
     * // 2025-12-31
     * $this->isStartOfMonth();  // false
     * ```
     *
     * @see self::startOfMonth()
     */
    public function isStartOfMonth(): bool
    {
        return $this->day()->is(DayOfMonth::first());
    }

    /**
     * Checks if the day of the month of this date is the last in the month.
     *
     * ```
     * // 2025-12-31
     * $this->isEndOfMonth();  // true
     *
     * // 2025-12-01
     * $this->isEndOfMonth();  // false
     * ```
     *
     * @see self::endOfMonth()
     */
    public function isEndOfMonth(): bool
    {
        return $this->day()->is($this->month()->lastDay($this->year()));
    }

    /**
     * Checks if this date or its unit is equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->is($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->is($other);  // false
     *
     * // 2025-12-30 vs Month::December
     * $this->is($other);  // true
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function is(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this date or its unit is not equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->isNot($other);  // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->isNot($other);  // false
     *
     * // 2025-12-30 vs Month::December
     * $this->isNot($other);  // false
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function isNot(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this date or its unit is before another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->isBefore($other);  // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->isBefore($other);  // false
     *
     * // 2025-12-30 vs Year::of(2026)
     * $this->isBefore($other);  // true
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function isBefore(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this date or its unit is before or equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 2025-12-31 vs 2025-12-30
     * $this->isBeforeOrEqualTo($other);  // false
     *
     * // 2025-12-31 vs DayOfMonth::of(30)
     * $this->isBeforeOrEqualTo($other);  // false
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function isBeforeOrEqualTo(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this date or its unit is after or equal to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-30
     * $this->isAfterOrEqualTo($other);  // true
     *
     * // 2025-12-30 vs 2025-12-31
     * $this->isAfterOrEqualTo($other);  // false
     *
     * // 2025-12-30 vs Year::of(2025)
     * $this->isAfterOrEqualTo($other);  // true
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function isAfterOrEqualTo(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this date or its unit is after another one.
     *
     * ```
     * // 2025-12-31 vs 2025-12-30
     * $this->isAfter($other);  // true
     *
     * // 2025-12-31 vs 2025-12-31
     * $this->isAfter($other);  // false
     *
     * // 2025-12-31 vs Year::of(2025)
     * $this->isAfter($other);  // false
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function isAfter(self|DateUnit $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this date or its unit to another one.
     *
     * ```
     * // 2025-12-30 vs 2025-12-31
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     *
     * // 2025-12-30 vs 2025-12-30
     * $this->compareTo($other)->equal();        // true
     * $this->compareTo($other)->less();         // false
     * $this->compareTo($other)->lessOrEqual();  // true
     *
     * // 2025-12-30 vs DayOfMonth::of(31)
     * $this->compareTo($other)->equal();        // false
     * $this->compareTo($other)->less();         // true
     * ```
     *
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day of month
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     */
    public function compareTo(self|DateUnit $other): Compared
    {
        return $this->instant()->compareTo($this->normalize($other)->instant());
    }

    /**
     * Returns this date formatted according to a given string
     * and using the global formatter or a given one.
     *
     * Supports {@see \DateTimeInterface::format()} syntax by default.
     * Non year, month, week and day characters will be printed as-is.
     *
     * @param non-empty-string $format
     *
     * @return non-empty-string
     *
     * @throws Exception\FormatError
     *
     * @see \Kronika\formatter()
     * @see \Kronika\Format\native() formatter
     */
    public function format(string $format, ?Formatter $formatter = null): string
    {
        return ($formatter ?? formatter())->format($this, $format);
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%s-%02d-%02d', $this->year(), $this->month()->number(), $this->day()->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['date' => (string)$this];
    }

    /** @internal {@see DateTime::with()} */
    #[\Override]
    public function _withinDateTime(LocalDateTime $datetime, bool $rolling): LocalDateTime
    {
        return $this->at($datetime->time());
    }

    private static function fromInstant(Instant $instant): self
    {
        return self::map($instant, static function (Instant $instant): self {
            return self::of(...\sscanf(\gmdate('x-m-d', $instant->second()), '%d-%d-%d'));
        });
    }

    private function instant(): Instant
    {
        return $this->remember(static fn(self $date): Instant => Instant::of(
            \strtotime("$date UTC"),
        ), key: Instant::class);
    }

    private function normalize(self|DateUnit $date): self
    {
        return $date instanceof DateUnit ? $this->with($date) : $date;
    }
}
