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
use Kronika\Exception\MalformedString;
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
 * @method static static|null tryOfFormat(string $format, ?string $datetime, ?Formatter $formatter = null)
 * @method static static|null tryParse(?string $datetime)
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
    public static function ofDateTime(DateTime|Native $datetime): self
    {
        if ($datetime instanceof self) {
            return $datetime;
        }
        if ($datetime instanceof ZonedDateTime) {
            return $datetime->toLocalDateTime();
        }

        return self::map($datetime, static function(Native $datetime): self {
            return self::of(Date::ofDateTime($datetime), Time::ofDateTime($datetime));
        }, when: static fn(Native $datetime): bool => $datetime instanceof \DateTimeImmutable);
    }

    /**
     * Obtains an instance of `LocalDateTime` from `Instant`.
     */
    public static function ofInstant(Instant $instant): self
    {
        return self::map($instant, static function(Instant $instant): self {
            return self::of(Date::ofInstant($instant), Time::ofInstant($instant));
        }, remember: 'instant');
    }

    /**
     * Obtains an instance of `LocalDateTime` from a given format and date-time string.
     *
     * @param non-empty-string $format
     * @param non-empty-string $datetime
     *
     * @throws Exception\FormatError
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
     * @throws MalformedString
     */
    public static function parse(string $datetime): self
    {
        if ($datetime === '' || \in_array(\strtolower($datetime), ['now', 'today'], strict: true)) {
            throw new MalformedString\DateTimeMalformedString('Invalid date-time string');
        }

        try {
            return self::ofDateTime(new \DateTimeImmutable($datetime));
        } catch (\DateMalformedStringException $e) {
            throw MalformedString\DateTimeMalformedString::wrap($e);
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
    public function with(Unit $unit, bool $rolling = false): static
    {
        return $unit->_withinDateTime($this, $rolling);
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
    public function until(DateTime|Unit $end, Precision $precision = Precision::Micro): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant(), $precision);
    }

    #[\Override]
    public function difference(DateTime|Unit $other, Precision $precision = Precision::Micro): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant(), $precision);
    }

    #[\Override]
    public function is(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    #[\Override]
    public function isNot(DateTime|Unit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
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
        return $this->instant()->compareTo($this->normalize($other)->instant(), $precision);
    }

    /** @throws Exception\FormatError */
    #[\Override]
    public function format(string $format, ?Formatter $formatter = null): string
    {
        return ($formatter ?? formatter())->format($this, $format);
    }

    #[\Override]
    public function toNative(?\DateTimeZone $timezone = null): \DateTimeImmutable
    {
        return new \DateTimeImmutable((string)$this, $timezone);
    }

    #[\Override]
    public function toNativeMutable(?\DateTimeZone $timezone = null): \DateTime
    {
        return new \DateTime((string)$this, $timezone);
    }

    #[\Override]
    public function instant(): Instant
    {
        return $this->remember(static function (self $that): Instant {
            return Instant::of(
                second: $that->date->instant()->second() + $that->time->instant()->second(),
                micro: $that->time->instant()->microsecond(),
            );
        }, key: __METHOD__);
    }

    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%s %s', $this->date, $this->time);
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
