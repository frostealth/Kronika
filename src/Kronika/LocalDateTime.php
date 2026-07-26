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
use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\DayOfYear;
use Kronika\Date\Month;
use Kronika\Date\Trait\HasDate;
use Kronika\Date\Year;
use Kronika\Format\DateTime\FormattedLocal as Formatted;
use Kronika\Format\DateTime\Formatter;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\Trait\HasTime;
use Kronika\Utils\Compared;
use Kronika\Utils\RefTrait;
use Kronika\Utils\RescueTrait;

/**
 * Represents a local date-time without time-zone.
 *
 * @method static static|null tryParse(?string $datetime)
 * @method static static|null tryFromFormat(string $format, ?string $datetime, ?Formatter $formatter = null)
 * @method static static|null tryOfFormat(string $format, ?string $datetime, ?Formatter $formatter = null) deprecated
 */
final readonly class LocalDateTime implements DateTime
{
    use HasDate;
    use HasTime;
    use RefTrait;
    use RescueTrait;

    /**
     * Obtains an instance of `LocalDateTime` from a date and time.
     *
     * ```
     * // 2025-12-31 12:15:30
     * $datetime = LocalDateTime::of(Date::of(2025, 12, 31), Time::of(12, 15, 30));
     *
     * // 2026-01-01 12:15:30.999999
     * $datetime = LocalDateTime::of(
     *     date: Date::of(2025, Month::December, 31),
     *     time: Time::of(12, 15, Second::of(30, 999999)),
     * );
     * ```
     */
    public static function of(Date $date, Time $time): self
    {
        return self::ref(date: $date, time: $time);
    }

    /**
     * Obtains an instance of `LocalDateTime` from a given date-time.
     */
    public static function fromDateTime(DateTime|Native $datetime): self
    {
        if ($datetime instanceof self) {
            return $datetime;
        }
        if ($datetime instanceof ZonedDateTime) {
            return $datetime->toLocalDateTime();
        }

        return self::map($datetime, static function(Native $datetime): self {
            return self::of(Date::fromDateTime($datetime), Time::fromDateTime($datetime));
        }, when: static fn(Native $datetime): bool => $datetime instanceof \DateTimeImmutable);
    }

    /**
     * Obtains an instance of `LocalDateTime` from a given format and date-time string.
     *
     * @param non-empty-string $format
     * @param non-empty-string $datetime
     *
     * @throws Exception\FormatError
     */
    public static function fromFormat(string $format, string $datetime, ?Formatter $formatter = null): self
    {
        $parsed = ($formatter ?? formatter())->parse(new Formatted($format, $datetime));

        return self::of(
            Date::of($parsed->year(), $parsed->month(), $parsed->day()),
            Time::of($parsed->hour(), $parsed->minute(Minute::zero(...)), $parsed->second(Second::zero(...)))
        );
    }

    /**
     * Obtains an instance of `LocalDateTime` from a given date-time string.
     *
     * ```
     * $datetime = LocalDateTime::parse(
     *     '2025-12-31 12:15:30.000999',
     * );
     * ```
     *
     * @param non-empty-string $datetime
     *
     * @throws Exception\MalformedString
     */
    public static function parse(string $datetime): self
    {
        if ($datetime === '' || \in_array(\strtolower($datetime), ['now', 'today'], strict: true)) {
            throw new Exception\MalformedString\DateTimeMalformedString('Invalid date-time string');
        }

        try {
            return self::fromDateTime(new \DateTime($datetime));
        } catch (\DateMalformedStringException $e) {
            throw Exception\MalformedString\DateTimeMalformedString::wrap($e);
        }
    }

    private function __construct(
        private Date $date,
        private Time $time,
    ) {
    }

    #[\Override]
    public function date(): Date
    {
        return $this->date;
    }

    #[\Override]
    public function year(): Year
    {
        return $this->date->year();
    }

    #[\Override]
    public function month(): Month
    {
        return $this->date->month();
    }

    #[\Override]
    public function day(): DayOfMonth
    {
        return $this->date->day();
    }

    #[\Override]
    public function dayOfWeek(): DayOfWeek
    {
        return $this->date->dayOfWeek();
    }

    #[\Override]
    public function dayOfYear(): DayOfYear
    {
        return $this->date->dayOfYear();
    }

    #[\Override]
    public function time(): Time
    {
        return $this->time;
    }

    #[\Override]
    public function hour(): Hour
    {
        return $this->time->hour();
    }

    #[\Override]
    public function minute(): Minute
    {
        return $this->time->minute();
    }

    #[\Override]
    public function second(): Second
    {
        return $this->time->second();
    }

    #[\Override]
    public function with(Unit $unit, OverflowMode $mode = OverflowMode::Clamp): static
    {
        return $unit->_withinDateTime($this, $mode);
    }

    /**
     * Combines this date-time with a given time-zone to create an instance of `ZonedDateTime`.
     *
     * ```
     * // 2025-12-31 12:15:30
     * $this->at(new \DateTimeZone('UTC'));  // 2025-12-31 12:15:30 UTC
     * ```
     */
    public function at(\DateTimeZone $timezone): ZonedDateTime
    {
        return ZonedDateTime::fromLocal($this, $timezone);
    }

    #[\Override]
    public function resetMicro(): static
    {
        return $this->with($this->second()->resetMicro());
    }

    #[\Override]
    public function resetSecond(): static
    {
        return $this->with(Second::zero());
    }

    #[\Override]
    public function add(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return self::fromDateTime($this->toNative(timezone_utc())->add($interval));
        }

        return self::fromInstant($this->instant()->add($interval));
    }

    #[\Override]
    public function sub(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return self::fromDateTime($this->toNative(timezone_utc())->sub($interval));
        }

        return self::fromInstant($this->instant()->sub($interval));
    }

    /**
     * Calculates the duration from this date-time or its unit to another one.
     *
     * ```
     * // 2025-12-10 10:15:30 vs 2025-12-20 12:30:45
     * $duration = $this->until($other);
     * $duration->days();    // 10
     * $duration->hours();   // 2
     * $duration->minutes(); // 15
     * $duration->seconds(); // 15
     * ```
     * ```
     * // 2025-12-10 10:15:30 vs 2025-11-01 00:00:00
     * $duration = $this->until($other);
     * $duration->days();    // 0
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     * ```
     * // DateTime vs Date
     * // 2025-12-10 10:15:30 vs Date::of(2025, 12, 20)
     * $duration = $this->until($other);
     * $duration->days();    // 10
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     *
     * @see \Kronika\Date
     * @see \Kronika\Date\Year
     * @see \Kronika\Date\Month
     * @see \Kronika\Date\DayOfMonth
     * @see \Kronika\Date\DayOfWeek
     * @see \Kronika\Date\DayOfYear
     * @see \Kronika\Time
     * @see \Kronika\Time\Hour
     * @see \Kronika\Time\Minute
     * @see \Kronika\Time\Second
     */
    public function until(self|Unit $end, Precision $precision = Precision::Micro): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant(), $precision);
    }

    /**
     * Calculates the duration between this date-time or its unit and another one.
     *
     * ```
     * // 2025-12-10 10:15:30 vs 2025-12-20 12:30:45
     * $duration = $this->difference($other);
     * $duration->days();    // 10
     * $duration->hours();   // 2
     * $duration->minutes(); // 15
     * $duration->seconds(); // 15
     * ```
     * ```
     * // 2025-12-10 10:15:30 vs 2025-11-01 00:00:00
     * $duration = $this->difference($other);
     * $duration->days();    // 0
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     * ```
     * // DateTime vs Date
     * // 2025-12-10 10:15:30 vs Date::of(2025, 12, 20)
     * $duration = $this->difference($other);
     * $duration->days();    // 10
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     *
     * @see \Kronika\Date
     * @see \Kronika\Date\Year
     * @see \Kronika\Date\Month
     * @see \Kronika\Date\DayOfMonth
     * @see \Kronika\Date\DayOfWeek
     * @see \Kronika\Date\DayOfYear
     * @see \Kronika\Time
     * @see \Kronika\Time\Hour
     * @see \Kronika\Time\Minute
     * @see \Kronika\Time\Second
     */
    public function difference(self|Unit $other, Precision $precision = Precision::Micro): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant(), $precision);
    }

    /**
     * Checks if this date-time or its unit is equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->is($other);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->is($other);  // false
     * $this->is($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->is($other, Precision::Second);  // false
     * $this->is($other, Precision::Minute);  // true
     *
     * // 2025-12-31 10:30:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->is($other, Precision::Minute);  // false
     *
     * // 2025-12-31 10:30:00.000000 vs Date::of(2025, 12, 31)
     * $this->is($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function is(self|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    /**
     * Checks if this date-time or its unit is not equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isNot($other);  // true
     * $this->isNot($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->isNot($other, Precision::Second);  // true
     * $this->isNot($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->isNot($other, Precision::Minute);  // true
     *
     * // 2026-01-01 00:00:00.000000 vs Date::of(2025, 12, 31)
     * $this->isNot($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isNot(self|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
    }

    /**
     * Checks if this date-time or its unit is before another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isBefore($other);  // true
     * $this->isBefore($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.999999
     * $this->isBefore($other, Precision::Second);  // true
     * $this->isBefore($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:59.999999
     * $this->isBefore($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs Date::of(2025, 12, 31)
     * $this->isBefore($other);  // false
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isBefore(self|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
    }

    /**
     * Checks if this date-time or its unit is before or equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 2025-12-31 10:15:30.999999 vs 2025-12-31 10:15:30.000000
     * $this->isBeforeOrEqualTo($other);  // false
     * $this->isBeforeOrEqualTo($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:59.999999 vs 2025-12-31 10:15:00.000000
     * $this->isBeforeOrEqualTo($other, Precision::Second);  // false
     * $this->isBeforeOrEqualTo($other, Precision::Minute);  // true
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:00.000000
     * $this->isBeforeOrEqualTo($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs Date::of(2025, 12, 31)
     * $this->isBeforeOrEqualTo($other);  // false
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isBeforeOrEqualTo(self|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    /**
     * Checks if this date-time or its unit is after or equal to another one.
     *
     * ```
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.000000
     * $this->isAfterOrEqualTo($other);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:30.999999
     * $this->isAfterOrEqualTo($other);  // false
     * $this->isAfterOrEqualTo($other, Precision::Second);  // true
     *
     * // 2025-12-31 10:15:30.000000 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqualTo($other, Precision::Second);  // false
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // true
     *
     * // 1990-01-01 23:59:59.999999 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // false
     *
     * // 1990-01-01 23:59:59.999999 vs 2025-12-31 10:15:59.000000
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // false
     *
     * // 1990-01-01 23:59:59.999999 vs Time::of(23, 59, 59)
     * $this->isAfterOrEqualTo($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isAfterOrEqualTo(self|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
    }

    /**
     * Checks if this date-time or its unit is after another one.
     *
     * ```
     * // 2025-12-31 10:15:30.999999 vs 2025-12-31 10:15:30.000000
     * $this->isAfter($other);  // true
     * $this->isAfter($other, Precision::Second);  // false
     *
     * // 2025-12-31 10:15:59.999999 vs 2025-12-31 10:15:30.000000
     * $this->isAfter($other, Precision::Second);  // true
     * $this->isAfter($other, Precision::Minute);  // false
     *
     * // 2026-01-01 00:00:00.000000 vs 2025-12-31 10:15:30.000000
     * $this->isAfter($other, Precision::Minute);  // true
     *
     * // 2026-01-01 10:15:55.000000 vs Time::of(10, 15, 30)
     * $this->isAfter($other);  // true
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isAfter(self|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
    }

    /**
     * Compares this date-time or its unit to another one.
     *
     * ```
     * // 2025-12-31 12:15:30 vs 2025-12-31 12:15:45
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     * $this->compareTo($other, Precision::Minute)->equal();  // true
     *
     * // 2025-12-31 12:15:30 vs Date::of(2025, 12, 31)
     * $this->compareTo($other)->equal();  // true
     * $this->compareTo($other)->less();   // false
     * ```
     *
     * @see \Kronika\Date – compare to a date
     * @see \Kronika\Date\Year – compare to a year
     * @see \Kronika\Date\Month – compare to a month
     * @see \Kronika\Date\DayOfMonth – compare to a day
     * @see \Kronika\Date\DayOfWeek – compare to a day of week
     * @see \Kronika\Date\DayOfYear – compare to a day of year
     * @see \Kronika\Time – compare to a time
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function compareTo(self|Unit $other, Precision $precision = Precision::Micro): Compared
    {
        return $this->instant()->compareTo($this->normalize($other)->instant(), $precision);
    }

    /** @throws Exception\FormatError */
    #[\Override]
    public function format(string $format, ?Formatter $formatter = null): string
    {
        return ($formatter ?? formatter())->format($this, $format);
    }

    /**
     * Obtains an instance of `\DateTimeImmutable` from this date-time.
     */
    public function toNative(\DateTimeZone $timezone): \DateTimeImmutable
    {
        return new \DateTimeImmutable((string)$this, $timezone);
    }

    /**
     * Obtains an instance of `\DateTime` from this date-time.
     */
    public function toNativeMutable(\DateTimeZone $timezone): \DateTime
    {
        return new \DateTime((string)$this, $timezone);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->remember(static fn(self $that): string => \sprintf(
            '%s %s',
            $that->date,
            $that->time,
        ), key: __FUNCTION__);
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return [
            'date' => (string)$this->date,
            'time' => (string)$this->time,
        ];
    }

    #[\Deprecated('use fromDateTime() instead.', since: '0.4.0')]
    public static function ofDateTime(DateTime|Native $datetime): self
    {
        return self::fromDateTime($datetime);
    }

    #[\Deprecated('use fromFormat() instead.', since: '0.4.0')]
    public static function ofFormat(string $format, string $datetime, ?Formatter $formatter = null): self
    {
        return self::fromFormat($format, $datetime, $formatter);
    }

    private static function fromInstant(Instant $instant): self
    {
        return self::map($instant, static function(Instant $instant): self {
            \sscanf(
                \gmdate('x-m-d H:i:s', $instant->second()),
                '%d-%d-%d %d:%d:%d',
                $year, $month, $day, $hour, $minute, $second,
            );

            return self::of(
                Date::of($year, $month, $day),
                Time::of($hour, $minute, Second::of($second, $instant->microsecond())),
            );
        }, remember: Instant::class);
    }

    private function instant(): Instant
    {
        return $this->remember(static fn(self $that): Instant => Instant::of(
            second: \strtotime("$that UTC"),
            micro: $that->second()->microsecond(),
        ), key: Instant::class);
    }

    private function normalize(self|Unit $datetime): self
    {
        return $datetime instanceof Unit ? $this->with($datetime) : $datetime;
    }
}
