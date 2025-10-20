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

final class ZonedDateTime extends \DateTimeImmutable implements DateTime
{
    public static function of(Date $date, Time $time, \DateTimeZone $timezone): self
    {
        return self::ofLocal(LocalDateTime::of($date, $time), $timezone);
    }

    public static function utcOf(Date $date, Time $time): self
    {
        return self::of($date, $time, new \DateTimeZone('utc'));
    }

    public static function ofLocal(LocalDateTime $local, \DateTimeZone $timezone): self
    {
        return new self($local, $timezone);
    }

    public static function midnightOf(Date $date, \DateTimeZone $timezone): self
    {
        return self::ofLocal(LocalDateTime::midnightOf($date), $timezone);
    }

    public static function ofDateTime(\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof self) {
            return $dateTime;
        }

        return self::ofLocal(LocalDateTime::ofDateTime($dateTime), $dateTime->getTimezone());
    }

    public static function ofTimestamp(int $timestamp): self
    {
        return self::ofInstant(Instant::of($timestamp), new \DateTimeZone('utc'));
    }

    public static function ofInstant(Instant $instant, \DateTimeZone $timezone): self
    {
        return LocalDateTime::ofInstant($instant)->atTimezone($timezone);
    }

    /**
     * @param non-empty-string $format
     * @param non-empty-string $datetime
     *
     * @throws \DateMalformedStringException
     */
    public static function ofFormat(string $format, string $datetime, ?\DateTimeZone $timezone = null): self
    {
        $native = \DateTimeImmutable::createFromFormat($format, $datetime, $timezone);
        if (! $native instanceof \DateTimeImmutable) {
            throw new \DateMalformedStringException();
        }

        return self::ofDateTime($native);
    }

    /**
     * @param non-empty-string $datetime
     *
     * @throws \DateMalformedStringException
     */
    public static function parse(string $datetime, ?\DateTimeZone $timezone = null): self
    {
        return self::ofDateTime(new \DateTimeImmutable($datetime, $timezone));
    }

    private function __construct(
        private readonly LocalDateTime $local,
        \DateTimeZone $timezone,
    ){
        parent::__construct((string)$local, $timezone);
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

    public function timestamp(): int
    {
        return parent::getTimestamp();
    }

    public function timezone(): \DateTimeZone
    {
        return $this->getTimezone();
    }

    /**
     * Change Date, Time, timezone or date/time units
     * (Year, Month, DayOfMonth, DayOfWeek, Hour, Minute, Second).
     */
    #[\Override]
    public function with(Unit|\DateTimeZone $unit): static
    {
        if ($unit instanceof \DateTimeZone) {
            return self::ofLocal($this->local, timezone: $unit);
        }

        return self::ofLocal($this->local->with($unit), $this->timezone());
    }

    public function shiftTimezone(\DateTimeZone $toTimezone): static
    {
        return self::ofDateTime(parent::setTimezone($toTimezone));
    }

    #[\Override]
    public function modify(string $modifier): static
    {
        return self::ofDateTime(parent::modify($modifier));
    }

    #[\Override]
    public function until(DateTime|\DateTimeInterface $end): Duration
    {
        if ($end instanceof LocalDateTime) {
            $end = $end->atTimezone($this->timezone());
        }

        return Duration::between(from: $this, to: $end);
    }

    #[\Override]
    public function add(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return self::ofDateTime(parent::add($interval));
        }

        return self::ofLocal($this->local->add($interval), $this->timezone());
    }

    #[\Override]
    public function sub(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return self::ofDateTime(parent::sub($interval));
        }

        return self::ofLocal($this->local->sub($interval), $this->timezone());
    }

    public function isInTheSameTimezoneAs(\DateTimeInterface $other): bool
    {
        return $this->isInTimezone($other->getTimezone());
    }

    public function isInTimezone(\DateTimeZone $timezone): bool
    {
        return Comparison::compare($this->timezone(), $timezone)->equal();
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

    public function compareTo(\DateTimeInterface $other): Comparison
    {
        return Comparison::compare($this->timestamp(), $other->getTimestamp());
    }

    public function toLocalDateTime(): LocalDateTime
    {
        return $this->local;
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
        return $this->format(\DateTimeInterface::ATOM);
    }

    #[\Override]
    public static function createFromMutable(\DateTime $object): static
    {
        return self::ofDateTime($object);
    }

    #[\Override]
    public static function createFromInterface(\DateTimeInterface $object): static
    {
        return self::ofDateTime($object);
    }

    #[\Override]
    public static function createFromTimestamp(float|int $timestamp): static
    {
        // @todo: microsecond
        return self::ofTimestamp($timestamp);
    }

    #[\Override]
    public static function createFromFormat(string $format, string $datetime, ?\DateTimeZone $timezone = null): static
    {
        return self::ofFormat($format, $datetime, $timezone);
    }

    /** @alias {@see self::with()} */
    #[\Override]
    public function setDate(int $year, int $month, int $day): static
    {
        return $this->with(Date::of($year, $month, $day));
    }

    #[\Override]
    public function setISODate(int $year, int $week, int $dayOfWeek = 1): static
    {
        return self::ofDateTime(parent::setISODate($year, $week, $dayOfWeek));
    }

    /** @alias {@see self::with()} */
    #[\Override]
    public function setTime(int $hour, int $minute, int $second = 0, int $microsecond = 0): static
    {
        return $this->with(Time::of($hour, $minute, $second));
    }

    /** @alias {@see self::timestamp()} */
    #[\Override]
    public function getTimestamp(): int
    {
        return $this->timestamp();
    }

    /** @alias {@see self::ofTimestamp()} */
    #[\Override]
    public function setTimestamp(int $timestamp): static
    {
        return self::ofTimestamp($timestamp);
    }

    /** @alias {@see self::shiftTimezone()} */
    #[\Override]
    public function setTimezone(\DateTimeZone $timezone): static
    {
        return $this->shiftTimezone($timezone);
    }

    #[\Override]
    public function getMicrosecond(): int
    {
        // @todo
        return 0;
    }

    #[\Override]
    public function setMicrosecond(int $microsecond): static
    {
        // @todo
        return parent::setMicrosecond($microsecond);
    }
}
