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
use Kronika\Exception\MalformedString\DateTimeMalformedString;
use Kronika\Format\DateTime\FormattedLocal as Formatted;
use Kronika\Format\DateTime\Formatter;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Utils\Compared;
use Kronika\Utils\WeakRefsTrait;

/**
 * Represents a local date-time without a time-zone.
 */
final readonly class LocalDateTime implements DateTime
{
    /** @use WeakRefsTrait<static, Date|Time> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of LocalDateTime from a date and time.
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
        return self::weak(date: $date, time: $time);
    }

    /**
     * Obtains an instance of LocalDateTime from a date-time with a time-zone.
     */
    public static function ofDateTime(DateTime|Native $dateTime): self
    {
        if ($dateTime instanceof self) {
            return $dateTime;
        }
        if ($dateTime instanceof ZonedDateTime) {
            return $dateTime->toLocalDateTime();
        }

        return self::of(Date::ofDateTime($dateTime), Time::ofDateTime($dateTime));
    }

    /**
     * Obtain an instance of LocalDateTime from a "Kronika\Instant".
     */
    public static function ofInstant(Instant $instant): self
    {
        return self::of(Date::ofInstant($instant), Time::ofInstant($instant));
    }

    /**
     * Obtain an instance of LocalDateTime from a given format and date-time string.
     *
     * @param non-empty-string $format
     * @param non-empty-string $datetime
     */
    public static function ofFormat(string $format, string $datetime, ?Formatter $formatter = null): self
    {
        $parsed = ($formatter ?? formatter())->parse(new Formatted($format, $datetime));

        return self::of(
            Date::of($parsed->year(), $parsed->month(), $parsed->day()),
            Time::of($parsed->hour(), $parsed->minute(Minute::zero(...)), $parsed->second(Second::zero(...)))
        );
    }

    /**
     * Obtains an instance of LocalDateTime from a given date-time string.
     *
     * @param non-empty-string $datetime
     *
     * @throws DateTimeMalformedString
     */
    public static function parse(string $datetime): self
    {
        try {
            return self::ofDateTime(new \DateTimeImmutable($datetime));
        } catch (\DateMalformedStringException $e) {
            throw DateTimeMalformedString::wrap($e);
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
    public function with(Unit $unit): static
    {
        return $unit->_withinDateTime($this);
    }

    /**
     * Returns an instance of ZonedDateTime from this date-time and a given time-zone.
     *
     * ```
     * // 2025-12-31 12:15:30
     * $this->at(new \DateTimeZone('UTC'));  // 2025-12-31 12:15:30 UTC
     * ```
     */
    public function at(\DateTimeZone $timezone): ZonedDateTime
    {
        return ZonedDateTime::ofLocal($this, $timezone);
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
            return self::ofDateTime($this->toNative()->add($interval));
        }

        return self::ofInstant($this->instant()->add($interval));
    }

    #[\Override]
    public function sub(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return self::ofDateTime($this->toNative()->sub($interval));
        }

        return self::ofInstant($this->instant()->sub($interval));
    }

    #[\Override]
    public function until(DateTime|Unit $end): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant());
    }

    #[\Override]
    public function difference(DateTime|Unit $other): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant());
    }

    #[\Override]
    public function isBefore(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
    }

    #[\Override]
    public function isBeforeOrEqualTo(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    #[\Override]
    public function isEqualTo(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    #[\Override]
    public function isNotEqualTo(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
    }

    #[\Override]
    public function isAfterOrEqualTo(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
    }

    #[\Override]
    public function isAfter(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
    }

    #[\Override]
    public function compareTo(DateTime|Unit $other, Precision $precision = Precision::Micro): Compared
    {
        return match($precision) {
            Precision::Micro => $this->instant()->compareTo($this->normalize($other)->instant()),
            Precision::Second => $this->resetMicro()->compareTo($this->normalize($other)->resetMicro()),
            Precision::Minute => $this->resetSecond()->compareTo($this->normalize($other)->resetSecond()),
        };
    }

    #[\Override]
    public function format(string $format, ?Formatter $formatter = null): string
    {
        return ($formatter ?? formatter())->format($this, $format);
    }

    /**
     * Returns an instance of LocalDateTime with the first day of the month.
     *
     * ```
     * // 2025-12-31 12:15:30
     * $this->toStartOfMonth();  // 2025-12-01 12:15:30
     * ```
     */
    public function toStartOfMonth(): static
    {
        return $this->with($this->date()->toStartOfMonth());
    }

    /**
     * Returns an instance of LocalDateTime with the last day of the month.
     *
     * ```
     * // 2025-02-01 12:15:30
     * $this->toEndOfMonth();  // 2025-02-28 12:15:30
     *
     * // 2024-02-01 12:15:30 – leap year
     * $this->toEndOfMonth();  // 2025-02-29 12:15:30
     * ```
     */
    public function toEndOfMonth(): static
    {
        return $this->with($this->date()->toEndOfMonth());
    }

    #[\Override]
    public function toNative(?\DateTimeZone $timezone = null): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u', (string)$this, $timezone);
    }

    #[\Override]
    public function toNativeMutable(?\DateTimeZone $timezone = null): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d\TH:i:s.u', (string)$this, $timezone);
    }

    #[\Override]
    public function instant(): Instant
    {
        return $this->date->instant()->join($this->time->instant());
    }

    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%sT%s', $this->date, $this->time);
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return [
            'date' => (string)$this->date,
            'time' => (string)$this->time,
        ];
    }

    private function normalize(DateTime|Unit $datetime): DateTime
    {
        return $datetime instanceof Unit ? $this->with($datetime) : $datetime;
    }
}
