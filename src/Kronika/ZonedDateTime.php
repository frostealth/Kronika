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
use Kronika\Format\DateTime\FormattedZoned as Formatted;
use Kronika\Format\DateTime\Formatter;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\Trait\HasTime;
use Kronika\Utils\Compared;
use Kronika\Utils\RefTrait;
use Kronika\Utils\RescueTrait;

/**
 * Represents a date-time with time-zone.
 *
 * @method static static|null tryOfFormat(string $format, ?string $datetime, ?\DateTimeZone $timezone = null, ?Formatter $formatter = null)
 * @method static static|null tryParse(?string $datetime, ?\DateTimeZone $timezone = null)
 */
final class ZonedDateTime extends \DateTimeImmutable implements DateTime
{
    use HasDate;
    use HasTime;
    use RefTrait;
    use RescueTrait;

    /**
     * Obtains an instance of `ZonedDateTime` from a date, time and time-zone.
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
        return self::ofStr(\sprintf('%s %s', $date, $time), $timezone);
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a date and time in UTC time-zone.
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
        return self::of($date, $time, timezone_utc());
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a local date-time and time-zone.
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
        return self::ofStr((string)$local, $timezone);
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a given date and time-zone with midnight time.
     *
     * ```
     * // 2025-12-31 00:00:00 +01:00
     * $datetime = ZonedDateTime::midnightOf(Date::of(2025, 12, 31), new \DateTimeZone('+01:00'));
     * ```
     *
     * @see \Kronika\Time::midnight()
     */
    public static function midnightOf(Date $date, \DateTimeZone $timezone): self
    {
        return self::of($date, Time::midnight(), $timezone);
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a date-time with time-zone.
     */
    public static function ofDateTime(Native $datetime): self
    {
        if ($datetime instanceof self) {
            return $datetime;
        }

        return self::createFromInterface($datetime);
    }

    /**
     * Obtains an instance of `ZonedDateTime` with UTC time-zone from a given timestamp.
     */
    public static function ofTimestamp(float|int $timestamp): self
    {
        return self::ofNative(\DateTimeImmutable::createFromTimestamp($timestamp), timezone_utc());
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a given `Kronika\Instant` and time-zone.
     */
    public static function ofInstant(Instant $instant, \DateTimeZone $timezone): self
    {
        return self::ofNative(\DateTimeImmutable::createFromTimestamp($instant->value())->setTimezone($timezone));
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a given format, date-time string and time-zone.
     *
     * If the date-time string doesn't contain time-zone, then a given time-zone will be used,
     * otherwise the system's time-zone will be used.
     *
     * @param non-empty-string $format
     * @param non-empty-string $datetime
     *
     * @throws Exception\FormatError
     */
    public static function ofFormat(
        string $format,
        string $datetime,
        ?\DateTimeZone $timezone = null,
        ?Formatter $formatter = null,
    ): self {
        $parsed = ($formatter ?? formatter())->parse(new Formatted($format, $datetime));

        return self::of(
            date: Date::of($parsed->year(), $parsed->month(), $parsed->day()),
            time: Time::of($parsed->hour(), $parsed->minute(Minute::zero(...)), $parsed->second(Second::zero(...))),
            timezone: $parsed->timezone(fn(): \DateTimeZone => $timezone ?? timezone_system()),
        );
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a given date-time string and time-zome.
     *
     * ```
     * $datetime = ZonedDateTime::parse(
     *     '2025-12-31 12:15:30.000999 +01:30',
     * );
     * ```
     *
     * @param non-empty-string $datetime
     *
     * @throws Exception\MalformedString
     */
    public static function parse(string $datetime, ?\DateTimeZone $timezone = null): self
    {
        if ($datetime === '' || \in_array(\strtolower($datetime), ['now', 'today'], strict: true)) {
            throw new Exception\MalformedString\DateTimeMalformedString('Invalid date-time string');
        }

        return new self($datetime, timezone: $timezone ?? timezone_system())->reference();
    }

    /** @throws Exception\MalformedString\DateTimeMalformedString */
    private function __construct(string $datetime, \DateTimeZone $timezone)
    {
        try {
            parent::__construct($datetime, $timezone);
        } catch (\DateMalformedStringException $e) {
            throw Exception\MalformedString\DateTimeMalformedString::wrap($e);
        }
    }

    #[\Override]
    public function date(): Date
    {
        return $this->local()->date();
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
    public function dayOfYear(): DayOfYear
    {
        return $this->date()->dayOfYear();
    }

    #[\Override]
    public function time(): Time
    {
        return $this->local()->time();
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
     * @return int<0, 999999>
     *
     * @psalm-suppress MoreSpecificReturnType
     */
    public function microsecond(): int
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $this->getMicrosecond();
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
        return $this->instant()->value();
    }

    /**
     * Shifts this date-time to a given time-zone.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->shift(new \DateTimeZone('+02:30'));  // 2025-12-31 13:45:30 +02:30
     * ```
     *
     * @see self::with() – change time-zone without changing time
     */
    public function shift(\DateTimeZone $to): static
    {
        return $this->isInTimezone($to) ? $this : $this->setTimezone($to);
    }

    /**
     * {@inheritDoc}
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->with(new \DateTimeZone('+02:30'));  // 2025-12-31 12:15:30 +02:30
     * ```
     *
     * @see self::shift() – change time-zone and shift time
     */
    #[\Override]
    public function with(Unit|\DateTimeZone $unit, bool $rolling = false): static
    {
        if ($unit instanceof \DateTimeZone) {
            return self::ofLocal($this->local(), timezone: $unit);
        }

        return self::ofLocal($this->local()->with($unit, $rolling), $this->timezone());
    }

    #[\Override]
    public function resetMicro(): static
    {
        return $this->setMicrosecond(0);
    }

    #[\Override]
    public function resetSecond(): static
    {
        return self::ofInstant($this->instant()->resetSecond(), $this->timezone());
    }

    #[\Override]
    public function until(DateTime|Unit|Native $end, Precision $precision = Precision::Micro): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant(), $precision);
    }

    #[\Override]
    public function difference(DateTime|Unit|Native $other, Precision $precision = Precision::Micro): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant(), $precision);
    }

    #[\Override]
    public function add(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return parent::add($interval)->reference();
        }

        return self::ofInstant($this->instant()->add($interval), $this->timezone());
    }

    #[\Override]
    public function sub(Duration|\DateInterval $interval): static
    {
        if ($interval instanceof \DateInterval) {
            return parent::sub($interval)->reference();
        }

        return self::ofInstant($this->instant()->sub($interval), $this->timezone());
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
     *
     * @see self::isInTheSameTimezoneAs()
     */
    public function isInTimezone(\DateTimeZone $timezone): bool
    {
        return $this->timezone()->getName() === $timezone->getName();
    }

    /**
     * Checks if this date-time is DST.
     *
     * ```
     * // 2006-04-02 03:00:00 America/New_York
     * $this->isDaylightSavingTime();  // true
     * ```
     */
    public function isDaylightSavingTime(): bool
    {
        return parent::format('I') === '1';
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
        return $this->instant()->compareTo($this->normalize($other)->instant(), $precision);
    }

    /** @throws Exception\FormatError */
    #[\Override]
    public function format(string $format, ?Formatter $formatter = null): string
    {
        return ($formatter ?? formatter())->format($this, $format);
    }

    /**
     * Returns an instance of `LocalDateTime` from this date-time.
     *
     * ```
     * // 2025-12-31 12:15:30 +01:00
     * $this->toLocalDateTime();  // 2025-12-31 12:15:30
     * ```
     */
    public function toLocalDateTime(): LocalDateTime
    {
        return $this->local();
    }

    #[\Override]
    public function toNative(): \DateTimeImmutable
    {
        return $this->remember(
            static fn(self $that): \DateTimeImmutable => \DateTimeImmutable::createFromInterface($that),
            key: \DateTimeImmutable::class,
        );
    }

    #[\Override]
    public function toNativeMutable(): \DateTime
    {
        return \DateTime::createFromImmutable($this);
    }

    #[\Override]
    public function instant(): Instant
    {
        return $this->remember(static fn(self $that): Instant => Instant::of(
            second: $that->getTimestamp(),
            micro: $that->getMicrosecond(),
        ), key: Instant::class);
    }

    #[\Override]
    public function __toString(): string
    {
        return parent::format('x-m-d H:i:s.u e');
    }

    /** @alias {@see self::ofDateTime()} */
    #[\Override]
    public static function createFromMutable(\DateTime $object): static
    {
        return parent::createFromMutable($object)->reference();
    }

    /** @alias {@see self::ofDateTime()} */
    #[\Override]
    public static function createFromInterface(Native $object): static
    {
        return parent::createFromInterface($object)->reference();
    }

    /** @see self::ofTimestamp() */
    #[\Override]
    public static function createFromTimestamp(float|int $timestamp): static
    {
        return parent::createFromTimestamp($timestamp)->reference();
    }

    /** @see self::ofFormat() */
    #[\Override]
    public static function createFromFormat(
        string $format,
        string $datetime,
        ?\DateTimeZone $timezone = null,
    ): static|false {
        $instance = parent::createFromFormat($format, $datetime, $timezone);

        return $instance instanceof self ? $instance->reference() : false;
    }

    /**
     * @throws Exception\MalformedString\DateTimeMalformedString
     *
     * @see self::with()
     * @see self::add()
     * @see self::sub()
     */
    #[\Override]
    public function modify(string $modifier): static
    {
        try {
            /** @psalm-suppress PossiblyFalseReference */
            return parent::modify($modifier)->reference();
        } catch (\DateMalformedStringException $e) {
            throw Exception\MalformedString\DateTimeMalformedString::wrap($e);
        }
    }

    /** @see self::with() */
    #[\Override]
    public function setDate(int $year, int $month, int $day): static
    {
        return parent::setDate($year, $month, $day)->reference();
    }

    #[\Override]
    public function setISODate(int $year, int $week, int $dayOfWeek = 1): static
    {
        return parent::setISODate($year, $week, $dayOfWeek)->reference();
    }

    /** @see self::with() */
    #[\Override]
    public function setTime(int $hour, int $minute, int $second = 0, int $microsecond = 0): static
    {
        return parent::setTime($hour, $minute, $second, $microsecond)->reference();
    }

    /** @see self::ofTimestamp() */
    #[\Override]
    public function setTimestamp(int $timestamp): static
    {
        return parent::setTimestamp($timestamp)->reference();
    }

    /** @see self::shift() */
    #[\Override]
    public function setTimezone(\DateTimeZone $timezone): static
    {
        return parent::setTimezone($timezone)->reference();
    }

    /** @see self::with() */
    #[\Override]
    public function setMicrosecond(int $microsecond): static
    {
        return parent::setMicrosecond($microsecond)->reference();
    }

    private static function ofNative(Native $datetime, ?\DateTimeZone $timezone = null): static
    {
        return self::ofStr($datetime->format('x-m-d H:i:s.u'), timezone: $timezone ?? $datetime->getTimezone());
    }

    /** @param string $datetime "x-m-d H:i:s.u" */
    private static function ofStr(string $datetime, \DateTimeZone $timezone): static
    {
        return self::ref(datetime: $datetime, timezone: $timezone);
    }

    private function local(): LocalDateTime
    {
        return $this->remember(static fn(self $that): LocalDateTime => LocalDateTime::ofDateTime(
            $that->toNativeMutable(),
        ), key: LocalDateTime::class);
    }

    private function normalize(DateTime|Unit|Native $datetime): self
    {
        return match (true) {
            $datetime instanceof Native => self::ofDateTime($datetime),
            $datetime instanceof Unit => $this->with($datetime),
            $datetime instanceof LocalDateTime => self::ofLocal($datetime, $this->timezone()),
        };
    }

    private function reference(): self  // @todo: rename
    {
        return self::ref(
            fn(...$args): self => $this,
            datetime: parent::format('x-m-d H:i:s.u'),
            timezone: parent::getTimezone(),
        );
    }
}
