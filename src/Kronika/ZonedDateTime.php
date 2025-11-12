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
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Exception\MalformedString;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Utils\Compared;
use Kronika\Utils\WeakRefsTrait;
use function Kronika\Utils\Math\double;

/**
 * Represents a date-time with time-zone.
 *
 * @psalm-import-type TMicrosecond from Second
 */
final class ZonedDateTime extends \DateTimeImmutable implements DateTime
{
    /** @use WeakRefsTrait<static, LocalDateTime|\DateTimeZone> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of ZonedDateTime from a date, time and time-zone.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $datetime = ZonedDateTime::of(
     *     date: Date::of(2025, 12, 31),
     *     time: Time::of(12, 15, 30),
     *     timezone: new \DateTimeZone('+01:00'),
     * );
     *
     * // 2025-12-31 12:15:30.999999 +02:00
     * $datetime = ZonedDateTime::of(
     *     date: Date::of(2025, 12, 31),
     *     time: Time::of(12, 15, Second::of(30, 999999)),
     *     timezone: new \DateTimeZone("+02:00"),
     * );
     * ```
     */
    public static function of(Date $date, Time $time, \DateTimeZone $timezone): self
    {
        return self::ofLocal(LocalDateTime::of($date, $time), $timezone);
    }

    /**
     * Obtains an instance of ZonedDateTime from a date and time in UTC time-zone.
     *
     * ```
     * // 2025-12-31 12:15:30 UTC
     * $datetime = ZonedDateTime::utcOf(
     *     date: Date::of(2025, 12, 31),
     *     time: Time::of(12, 15, 30),
     * );
     * ```
     */
    public static function utcOf(Date $date, Time $time): self
    {
        return self::of($date, $time, new \DateTimeZone('UTC'));
    }

    /**
     * Obtains an instance of ZonedDateTime from a local date-time and time-zone.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $datetime = ZonedDateTime::of(
     *     LocalDateTime::of(Date::of(2025, 12, 31), Time::of(12, 15, 30)),
     *     new \DateTimeZone('+01:00'),
     * );
     * ```
     */
    public static function ofLocal(LocalDateTime $local, \DateTimeZone $timezone): self
    {
        return self::weak(local: $local, timezone: $timezone);
    }

    /**
     * Obtains an instance of LocalDateTime from a given date and time-zone with midnight time.
     *
     * ```
     * // 2025-12-31 00:00:00 +01:00
     * $datetime = ZonedDateTime::midnightOf(Date::of(2025, 12, 31), new \DateTimeZone('+01:00'));
     * ```
     */
    public static function midnightOf(Date $date, \DateTimeZone $timezone): self
    {
        return self::ofLocal($date->at(Time::midnight()), $timezone);
    }

    /**
     * Obtains an instance of ZonedDateTime from a date-time with time-zone.
     */
    public static function ofDateTime(Native $dateTime): self
    {
        if ($dateTime instanceof self) {
            return $dateTime;
        }

        return self::ofLocal(LocalDateTime::ofDateTime($dateTime), $dateTime->getTimezone());
    }

    /**
     * Obtain an instance of ZonedDateTime with UTC time-zone from a given timestamp.
     */
    public static function ofTimestamp(float|int $timestamp): self
    {
        return self::ofInstant(Instant::ofValue($timestamp), new \DateTimeZone('UTC'));
    }

    /**
     * Obtain an instance of ZonedDateTime from a given "Kronika\Instant" and time-zone.
     */
    public static function ofInstant(Instant $instant, \DateTimeZone $timezone): self
    {
        return self::ofLocal(LocalDateTime::ofInstant($instant), $timezone);
    }

    /**
     * Obtain an instance of ZonedDateTime from a given format, date-time string and time-zone.
     *
     * @param non-empty-string $format
     * @param non-empty-string $datetime
     *
     * @throws Exception\FormatError
     */
    public static function ofFormat(string $format, string $datetime, ?\DateTimeZone $timezone = null): self
    {
        $native = \DateTimeImmutable::createFromFormat($format, $datetime, $timezone);
        if (! $native instanceof \DateTimeImmutable) {
            throw new Exception\FormatError("Failed to parse [$datetime] with format [$format]");
        }

        return self::ofDateTime($native);
    }

    /**
     * Obtains an instance of ZonedDateTime from a given date-time string and time-zome.
     *
     * @param non-empty-string $datetime
     *
     * @throws MalformedString
     */
    public static function parse(string $datetime, ?\DateTimeZone $timezone = null): self
    {
        try {
            return self::ofDateTime(new \DateTimeImmutable($datetime, $timezone));
        } catch (\DateMalformedStringException $e) {
            throw MalformedString\DateTimeMalformedString::wrap($e);
        }
    }

    private function __construct(
        private readonly LocalDateTime $local,
        \DateTimeZone $timezone,
    ){
        parent::__construct(\sprintf('%s %s', $local->date(), $local->time()), $timezone);
    }

    #[\Override]
    public function date(): Date
    {
        return $this->local->date();
    }

    #[\Override]
    public function time(): Time
    {
        return $this->local->time();
    }

    #[\Override]
    public function year(): Year
    {
        return $this->date()->year();
    }

    #[\Override]
    public function month(): Month
    {
        return $this->date()->month();
    }

    #[\Override]
    public function day(): DayOfMonth
    {
        return $this->date()->day();
    }

    #[\Override]
    public function dayOfWeek(): DayOfWeek
    {
        return $this->date()->dayOfWeek();
    }

    #[\Override]
    public function hour(): Hour
    {
        return $this->time()->hour();
    }

    #[\Override]
    public function minute(): Minute
    {
        return $this->time()->minute();
    }

    #[\Override]
    public function second(): Second
    {
        return $this->time()->second();
    }

    /**
     * Returns the microsecond of this date-time.
     *
     * @return TMicrosecond
     */
    public function microsecond(): int
    {
        return $this->second()->microsecond();
    }

    /**
     * Returns the time-zone of this date-time.
     */
    public function timezone(): \DateTimeZone
    {
        /** @psalm-ignore-falsable-return */
        return $this->getTimezone();
    }

    /**
     * Returns the timestamp of this date-time.
     */
    public function timestamp(): float
    {
        return double([parent::getTimestamp(), $this->microsecond()]);
    }

    /**
     * Shifts this date-time to a given time-zone.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->shift(new \DateTimeZone('+02:30'));  // 2025-12-31 13:45:30 +02:30
     * ```
     */
    public function shift(\DateTimeZone $to): static
    {
        return $this->isInTimezone($to) ? $this : self::ofDateTime($this->toNative()->setTimezone($to));
    }

    /** @deprecated {@see self::shift()} */
    public function shiftTimezone(\DateTimeZone $to): static
    {
        return $this->shift($to);
    }

    /**
     * {@inheritDoc}
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->with(new \DateTimeZone('+02:30'));  // 2025-12-31 12:15:30 +02:30
     * ```
     */
    #[\Override]
    public function with(Unit|\DateTimeZone $unit): static
    {
        if ($unit instanceof \DateTimeZone) {
            return self::ofLocal($this->local, timezone: $unit);
        }

        return self::ofLocal($this->local->with($unit), $this->timezone());
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
    public function until(DateTime|Unit|Native $end): Duration
    {
        return $this->local->until($this->localize($end));
    }

    #[\Override]
    public function difference(DateTime|Unit|Native $other): Duration
    {
        return $this->local->difference($this->localize($other));
    }

    #[\Override]
    public function add(Duration|\DateInterval $interval): static
    {
        return self::ofLocal($this->local->add($interval), $this->timezone());
    }

    #[\Override]
    public function sub(Duration|\DateInterval $interval): static
    {
        return self::ofLocal($this->local->sub($interval), $this->timezone());
    }

    /**
     * Checks if this date-time is in the same time-zone as another one.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00 vs 2025-12-31 15:00:00 +01:00
     * $this->isInTheSameTimezone($other);  // true
     * ```
     * ```
     * // 2025-12-31 12:15:30 +01:00 vs 2025-12-31 15:00:00 UTC
     * $this->isInTheSameTimezone($other);  // false
     * ```
     * ```
     * // 2025-12-31 12:15:30 +00:00 vs 2025-12-31 15:00:00 UTC
     * $this->isInTheSameTimezone($other);  // false
     * ```
     *
     * @see self::isInTimezone()
     */
    public function isInTheSameTimezoneAs(self|Native $other): bool
    {
        return $this->isInTimezone($other->getTimezone());
    }

    /**
     * Checks if the time-zone name of this date-time is equal to another one.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->isInTimezone(new \DateTimeZone("+01:00"));  // true
     * $this->isInTimezone(new \DateTimeZone("+00:00"));  // false
     * ```
     * ```
     * // 2025-12-31 12:15:30 UTC
     * $this->isInTimezone(new \DateTimeZone("+00:00"));  // false
     * $this->isInTimezone(new \DateTimeZone("UTC"));     // true
     * ```
     */
    public function isInTimezone(\DateTimeZone $timezone): bool
    {
        return $this->timezone()->getName() === $timezone->getName();
    }

    #[\Override]
    public function is(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    #[\Override]
    public function isNot(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
    }

    #[\Override]
    public function isBefore(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
    }

    #[\Override]
    public function isBeforeOrEqualTo(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    /** @deprecated {@see self::is()} */
    #[\Override]
    public function isEqualTo(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    /** @deprecated {@see self::isNot()} */
    #[\Override]
    public function isNotEqualTo(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
    }

    #[\Override]
    public function isAfterOrEqualTo(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
    }

    #[\Override]
    public function isAfter(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
    }

    #[\Override]
    public function compareTo(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): Compared
    {
        return $this->local->compareTo($this->localize($other), $precision);
    }

    /**
     * Returns an instance of LocalDateTime from this date-time.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->toLocalDateTime();  // 2025-12-31 12:15:30
     * ```
     */
    public function toLocalDateTime(): LocalDateTime
    {
        return $this->local;
    }

    /**
     * Returns an instance of ZonedDateTime with the first day of the month.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->toStartOfMonth();  // 2025-12-01 12:15:30 +01:00
     * ```
     */
    public function toStartOfMonth(): static
    {
        return $this->with($this->date()->toStartOfMonth());
    }

    /**
     * Returns an instance of ZonedDateTime with the last day of the month.
     *
     * ```
     * // 2025-02-01 12:15:30 +01:00
     * $this->toEndOfMonth();  // 2025-02-28 12:15:30 +01:00
     *
     * // 2024-02-01 – leap year
     * $this->toEndOfMonth();  // 2025-02-29 12:15:30 +01:00
     * ```
     */
    public function toEndOfMonth(): static
    {
        return $this->with($this->date()->toEndOfMonth());
    }

    #[\Override]
    public function toNative(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromInterface($this);
    }

    #[\Override]
    public function toNativeMutable(): \DateTime
    {
        return \DateTime::createFromImmutable($this);
    }

    #[\Override]
    public function instant(): Instant
    {
        return $this->local->instant();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->format('Y-m-d\TH:i:s.uP');
    }

    /** @alias {@see self::ofDateTime()} */
    #[\Override]
    public static function createFromMutable(\DateTime $object): static
    {
        return self::ofDateTime($object);
    }

    /** @alias {@see self::ofDateTime()} */
    #[\Override]
    public static function createFromInterface(Native $object): static
    {
        return self::ofDateTime($object);
    }

    /** @see self::ofFormat() */
    #[\Override]
    public static function createFromFormat(string $format, string $datetime, ?\DateTimeZone $timezone = null): static
    {
        return self::ofDateTime(\DateTimeImmutable::createFromFormat($format, $datetime, $timezone));
    }

    /** @see self::with() */
    #[\Override]
    public function modify(string $modifier): static
    {
        return self::ofDateTime($this->toNative()->modify($modifier));
    }

    /** @see self::with() */
    #[\Override]
    public function setDate(int $year, int $month, int $day): static
    {
        return self::ofDateTime($this->toNative()->setDate($year, $month, $day));
    }

    #[\Override]
    public function setISODate(int $year, int $week, int $dayOfWeek = 1): static
    {
        return self::ofDateTime($this->toNative()->setISODate($year, $week, $dayOfWeek));
    }

    /** @see self::with() */
    #[\Override]
    public function setTime(int $hour, int $minute, int $second = 0, int $microsecond = 0): static
    {
        return self::ofDateTime($this->toNative()->setTime($hour, $minute, $second, $microsecond));
    }

    /** @see self::ofTimestamp() */
    #[\Override]
    public function setTimestamp(int $timestamp): static
    {
        return self::ofDateTime($this->toNative()->setTimestamp($timestamp));
    }

    /** @see self::shift() */
    #[\Override]
    public function setTimezone(\DateTimeZone $timezone): static
    {
        return self::ofDateTime($this->toNative()->setTimezone($timezone));
    }

    private function localize(DateTime|Unit|Native $datetime): DateTime
    {
        if ($datetime instanceof LocalDateTime) {
            return $datetime;
        }
        if ($datetime instanceof Unit) {
            return $this->with($datetime);
        }

        return self::ofDateTime($datetime)->shift($this->timezone());
    }
}
