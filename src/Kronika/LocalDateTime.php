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

use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Utils\Comparison;

/**
 * Represents a local date-time without a time-zone.
 */
final readonly class LocalDateTime implements DateTime
{
    /**
     * Obtains an instance of LocalDateTime from a date and time.
     */
    public static function of(Date $date, Time $time): self
    {
        return new self(date: $date, time: $time);
    }

    public static function midnightOf(Date $date): self
    {
        return self::of($date, Time::midnight());
    }

    public static function middayOf(Date $date): self
    {
        return self::of($date, Time::midday());
    }

    public static function endOfDayOf(Date $date): self
    {
        return self::of($date, Time::endOfDay());
    }

    /**
     * Obtains an instance of LocalDateTime from a date-time with a time-zone.
     */
    public static function ofDateTime(DateTime|\DateTimeInterface $dateTime): self
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
        return $unit->withinDateTime($this);
    }

    public function atTimezone(\DateTimeZone $timezone): ZonedDateTime
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
    public function until(DateTime $end): Duration
    {
        return $this->instant()->until(self::ofDateTime($end)->instant());
    }

    #[\Override]
    public function isBefore(DateTime $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
    }

    #[\Override]
    public function isBeforeOrEqual(DateTime $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    #[\Override]
    public function isEqualTo(DateTime $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    #[\Override]
    public function isNotEqualTo(DateTime $other, Precision $precision = Precision::Micro): bool
    {
        return ! $this->isEqualTo($other, $precision);
    }

    #[\Override]
    public function isAfterOrEqual(DateTime $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
    }

    #[\Override]
    public function isAfter(DateTime $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
    }

    #[\Override]
    public function compareTo(DateTime $other, Precision $precision = Precision::Micro): Comparison
    {
        $other = self::ofDateTime($other);

        return match($precision) {
            Precision::Micro => $this->instant()->compareTo($other->instant()),
            Precision::Second => $this->resetMicro()->compareTo($other->resetMicro()),
            Precision::Minute => $this->resetSecond()->compareTo($other->resetSecond()),
        };
    }

    #[\Override]
    public function format(string $format): string
    {
        return $this->toNative()->format($format);
    }

    /**
     * @param non-empty-string $modifier
     *
     * @throws \DateMalformedStringException
     */
    public function modify(string $modifier): self
    {
        return self::ofDateTime($this->toNative()->modify($modifier));
    }

    public function toStartOfMonth(): static
    {
        return $this->with($this->date()->toStartOfMonth());
    }

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
        return $this->date->instant()->merge($this->time->instant());
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
            'date' => (string) $this->date,
            'time' => (string) $this->time,
        ];
    }
}
