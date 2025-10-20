<?php

declare(strict_types=1);

namespace Kronika;

use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;

final readonly class LocalDateTime implements DateTime
{
    private function __construct(
        private Date $date,
        private Time $time,
    ) {
    }

    public static function of(Date $date, Time $time): self
    {
        return new self(date: $date, time: $time);
    }

    public static function midnightOf(Date $date): self
    {
        return self::of($date, Time::midnight());
    }

    public static function endOfDayOf(Date $date): self
    {
        return self::of($date, Time::endOfDay());
    }

    public static function ofDateTime(DateTime|\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof self) {
            return $dateTime;
        }
        if ($dateTime instanceof ZonedDateTime) {
            return $dateTime->toLocalDateTime();
        }

        return self::of(Date::fromDateTime($dateTime), Time::ofDateTime($dateTime));
    }

    public static function ofInstant(Instant $instant): self
    {
        return self::of(Date::fromInstant($instant), Time::ofInstant($instant));
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
        if ($end instanceof ZonedDateTime) {
            return $this->atTimezone($end->timezone())->until($end);
        }

        return $this->instant()->until($end->instant());
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
        return $this->instant()->compareTo($other->instant());
    }

    public function atTimezone(\DateTimeZone $timezone): ZonedDateTime
    {
        return ZonedDateTime::ofLocal($this, $timezone);
    }

    #[\Override]
    public function format(string $format): string
    {
        return $this->toNative()->format($format);
    }

    #[\Override]
    public function modify(string $modifier): static
    {
        return self::ofDateTime($this->toNative()->modify($modifier));
    }

    #[\Override]
    public function toStartOfMonth(): static
    {
        return $this->with($this->date()->toStartOfMonth());
    }

    #[\Override]
    public function toEndOfMonth(): static
    {
        return $this->with($this->date()->toEndOfMonth());
    }

    #[\Override]
    public function toNative(?\DateTimeZone $timezone = null): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', (string)$this, $timezone);
    }

    #[\Override]
    public function toNativeMutable(?\DateTimeZone $timezone = null): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d\TH:i:s', (string)$this, $timezone);
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
