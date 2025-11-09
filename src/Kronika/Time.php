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
use Kronika\Exception\MalformedString;
use Kronika\Format\Time\Formatted as Formatted;
use Kronika\Format\Time\Formatter;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;
use Kronika\Utils\Compared;
use Kronika\Utils\RefTrait;

/**
 * Represents a time.
 *
 * @psalm-import-type THour from Hour
 * @psalm-import-type TMinute from Minute
 * @psalm-import-type TSecond from Second
 */
final readonly class Time implements Unit
{
    use RefTrait;

    /**
     * Obtains an instance of `Time` from an hour, minute and second.
     *
     * ```
     * // 10:30:45
     * $time = Time::of(hour: 10, minute: 30, second: 45);
     * $time = Time::of(Hour::of(10), Minute::of(30), Second::of(45));
     *
     * // 11:45.55.000999
     * $time = Time::of(hour: 11, minute: 45, second: Second::of(55, 999));
     * ```
     *
     * @psalm-param Hour|THour     $hour
     * @psalm-param Minute|TMinute $minute
     * @psalm-param Second|TSecond $second
     *
     * @throws Exception\InvalidTime
     */
    public static function of(Hour|int $hour, Minute|int $minute, Second|int $second = 0): self
    {
        return self::ref(hour: Hour::of($hour), minute: Minute::of($minute), second: Second::of($second));
    }

    /**
     * Obtains an instance of `Time` at the start of the day ("00:00:00.000000").
     *
     * @see self::isMidnight()
     */
    public static function midnight(): self
    {
        static $instance = self::of(Hour::zero(), Minute::zero(), Second::zero());

        return $instance;
    }

    /**
     * Obtains an instance of `Time` at the middle of the day ("12:00:00.000000").
     *
     * @see self::isMidday()
     */
    public static function midday(): self
    {
        static $instance = self::of(Hour::of(12), Minute::zero(), Second::zero());

        return $instance;
    }

    /**
     * Obtains an instance of `Time` at the end of the day ("23:59:59.999999").
     *
     * @see self::isEndOfDay()
     */
    public static function endOfDay(): self
    {
        static $instance = self::of(Hour::last(), Minute::last(), Second::last());

        return $instance;
    }

    /**
     * Obtain an instance of `Time` from a date-time.
     */
    public static function ofDateTime(DateTime|Native $datetime): self
    {
        if ($datetime instanceof DateTime) {
            return $datetime->time();
        }

        return self::map($datetime, static function(Native $datetime): self {
            [$hour, $minute, $second, $micro] = \sscanf($datetime->format('H:i:s.u'), '%u:%u:%u.%u');

            return self::of($hour, $minute, Second::of($second, $micro));
        }, when: static fn(Native $datetime): bool => $datetime instanceof \DateTimeImmutable);
    }

    /**
     * Obtain an instance of `Time` from a timestamp.
     */
    public static function ofTimestamp(float|int $timestamp): self
    {
        return self::ofInstant(Instant::ofValue($timestamp));
    }

    /**
     * Obtain an instance of `Time` from a `Kronika\Instant`.
     */
    public static function ofInstant(Instant $instant): self
    {
        return self::map($instant, static function (Instant $instant): self {
            ['hours' => $hour, 'minutes' => $minute, 'seconds' => $second] = \getdate($instant->second());

            return self::of($hour, $minute, Second::of($second, $instant->microsecond()));
        });
    }

    /**
     * Obtain an instance of `Time` from a given format and time string.
     *
     * @param non-empty-string $format
     * @param non-empty-string $time
     *
     * @throws Exception\FormatError
     */
    public static function ofFormat(string $format, string $time, ?Formatter $formatter = null): self
    {
        $parsed = ($formatter ?? formatter())->parse(new Formatted($format, $time));

        return self::of($parsed->hour(), $parsed->minute(), $parsed->second(Second::zero(...)));
    }

    /**
     * Obtains an instance of `Time` from a given time string.
     *
     * ```
     * $time = Time::parse('12:15:30.000999');
     * ```
     *
     * @param non-empty-string $time
     *
     * @throws MalformedString
     */
    public static function parse(string $time): self
    {
        if ($time === '' || \in_array(\strtolower($time), ['now', 'today'], strict: true)) {
            throw new MalformedString\TimeMalformedString('Invalid time string');
        }

        try {
            return self::ofDateTime(new \DateTimeImmutable($time));
        } catch (\DateMalformedStringException $e) {
            throw MalformedString\TimeMalformedString::wrap($e);
        }
    }

    private function __construct(
        private Hour $hour,
        private Minute $minute,
        private Second $second,
    ){
    }

    /**
     * Returns an instance of `Hour` from this time.
     */
    public function hour(): Hour
    {
        return $this->hour;
    }

    /**
     * Returns an instance of `Minute` from this time.
     */
    public function minute(): Minute
    {
        return $this->minute;
    }

    /**
     * Returns an instance of `Second` from this time.
     */
    public function second(): Second
    {
        return $this->second;
    }

    /**
     * Returns an instance of `Time` with a given time unit.
     *
     * ```
     * // 12:15:30
     * $this->with(Minute::of(30));  // 12:30:30
     * $this->with(Hour::of(21));    // 21:30:30
     * $this->with(Second::of(10));  // 12:30:10
     * ```
     *
     * @see \Kronika\Time\Hour – change only hour
     * @see \Kronika\Time\Minute – change only minute
     * @see \Kronika\Time\Second – change only second
     */
    public function with(TimeUnit $unit): self
    {
        return $unit->_withinTime($this);
    }

    /**
     * Returns an instance of `LocalDateTime` with a given date and this time.
     *
     * ```
     * // 12:15:30
     * $this->at(Date::of(2025, 12, 31));  // 2025-12-31 12:15:30
     * ```
     */
    public function at(Date $date): LocalDateTime
    {
        return $date->at($this);
    }

    /**
     * Resets a microsecond to 0.
     *
     * ```
     * // 10:15:30.999999
     * $time->resetMicro();  // 10:15:30.000000
     * ```
     */
    public function resetMicro(): self
    {
        return $this->with($this->second()->resetMicro());
    }

    /**
     * Resets a second and microsecond to 0.
     *
     * ```
     * // 10:15:30.999999
     * $time->resetSecond();  // 10:15:00.000000
     * ```
     */
    public function resetSecond(): self
    {
        return $this->with(Second::zero());
    }

    /**
     * Adds an amount of hours, minutes and seconds to this time.
     *
     * ```
     * // 10:15:30 + 2 hours and 45 minutes
     * $this->add(Duration::of(hours: 2, minutes: 45));  // 13:00:30
     *
     * // 10:15:30 + 120 minutes
     * $this->add(Duration::of(minutes: 120));  // 12:15:30
     * ```
     */
    public function add(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            $duration = Duration::of(hours: $duration->h, minutes: $duration->i, seconds: $duration->s);
        }

        return self::ofInstant($this->instant()->add($duration->dropToHours()));
    }

    /**
     * Subtracts an amount of hours, minutes and seconds from this time.
     *
     * ```
     * // 10:15:30 - 2 hours and 45 minutes
     * $this->sub(Duration::of(hours: 2, minutes: 45));  // 07:30:30
     *
     * // 10:15:30 - 120 minutes
     * $this->sub(Duration::of(minutes: 120));  // 08:15:30
     * ```
     */
    public function sub(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            $duration = Duration::of(hours: $duration->h, minutes: $duration->i, seconds: $duration->s);
        }

        return self::ofInstant($this->instant()->sub($duration->dropToHours()));
    }

    /**
     * Returns a duration from this time or its unit to another one.
     *
     * ```
     * // 10:15:30 vs 23:59:59
     * $duration = $this->until($other);
     * $duration->hours();   // 12
     * $duration->minutes(); // 44
     * $duration->seconds(); // 29
     * ```
     * ```
     * // 10:15:30 vs 00:00:00
     * $duration = $this->until($other);
     * $duration->hours();   // 0
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     * ```
     * // 10:15:30 vs Hour(12)
     * $duration = $this->until($other);
     * $duration->hours();   // 2
     * $duration->minutes(); // 0
     * $duration->seconds(); // 0
     * ```
     *
     * @see \Kronika\Time\Hour
     * @see \Kronika\Time\Minute
     * @see \Kronika\Time\Second
     */
    public function until(self|TimeUnit $end): Duration
    {
        return $this->instant()->until($this->normalize($end)->instant());
    }

    /**
     * Returns a duration between this time or its unit and another one.
     *
     * ```
     * // 10:15:30 vs 23:59:59
     * $duration = $this->difference($other);
     * $duration->hours();   // 12
     * $duration->minutes(); // 44
     * $duration->seconds(); // 29
     * ```
     * ```
     * // 10:15:30 vs 00:00:00
     * $duration = $this->difference($other);
     * $duration->hours();   // 10
     * $duration->minutes(); // 15
     * $duration->seconds(); // 30
     * ```
     *
     * @see \Kronika\Time\Hour
     * @see \Kronika\Time\Minute
     * @see \Kronika\Time\Second
     */
    public function difference(self|TimeUnit $other): Duration
    {
        return $this->instant()->difference($this->normalize($other)->instant());
    }

    /**
     * Checks if this time is midnight.
     *
     * ```
     * // 00:00:00.999999
     * $time->isMidnight();  // true
     * $time->isMidnight(Precision::Micro);   // false
     *
     * // 00:00:59.999999
     * $time->isMidnight();  // false
     * $time->isMidnight(Precision::Minute);  // true
     * ```
     *
     * @see self::midnight()
     */
    public function isMidnight(Precision $precision = Precision::Second): bool
    {
        return $this->isEqualTo(self::midnight(), $precision);
    }

    /**
     * Checks if this time is midday/noon.
     *
     * ```
     * // 12:00:00.999999
     * $time->isMidday();  // true
     * $time->isMidday(Precision::Micro);   // false
     *
     * // 12:00:59.999999
     * $time->isMidday();  // false
     * $time->isMidday(Precision::Minute);  // true
     * ```
     *
     * @see self::midday()
     */
    public function isMidday(Precision $precision = Precision::Second): bool
    {
        return $this->isEqualTo(self::midday(), $precision);
    }

    /**
     * Checks if this time or its unit is the end of the day.
     *
     * ```
     * // 23:59:59.000000
     * $time->isEndOfDay();  // true
     * $time->isEndOfDay(Precision::Micro);  // false
     *
     * // 23:59:01.999999
     * $time->isEndOfDay();  // false
     * $time->isEndOfDay(Precision::Minute);  // true
     * ```
     *
     * @see self::endOfDay()
     */
    public function isEndOfDay(Precision $precision = Precision::Second): bool
    {
        return $this->isEqualTo(self::endOfDay(), $precision);
    }

    /**
     * Checks if this time or its unit is before another one.
     *
     * ```
     * // 10:15:30.000000 vs 10:15:30.999999
     * $this->isBefore($other);  // true
     * $this->isBefore($other, Precision::Second);  // false
     *
     * // 10:15:30.000000 vs 10:15:59.999999
     * $this->isBefore($other, Precision::Second);  // true
     * $this->isBefore($other, Precision::Minute);  // false
     *
     * // 10:15:30.000000 vs Minute::of(15)
     * $this->isBefore($other);  // false
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isBefore(self|TimeUnit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
    }

    /**
     * Checks if this time or its unit is before or equal to another one.
     *
     * ```
     * // 10:15:30.000000 vs 10:15:30.000000
     * $this->isBeforeOrEqualTo($other);  // true
     *
     * // 10:15:30.999999 vs 10:15:30.000000
     * $this->isBeforeOrEqualTo($other);  // false
     * $this->isBeforeOrEqualTo($other, Precision::Second);  // true
     *
     * // 10:15:59.999999 vs 10:15:00.000000
     * $this->isBeforeOrEqualTo($other, Precision::Second);  // false
     * $this->isBeforeOrEqualTo($other, Precision::Minute);  // true
     *
     * // 10:15:59.999999 vs Minute::of(15)
     * $this->isBeforeOrEqualTo($other);  // true
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isBeforeOrEqualTo(self|TimeUnit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    /**
     * Checks if this time or its unit is equal to another one.
     *
     * ```
     * // 10:15:30.000000 vs 10:15:30.000000
     * $this->isEqualTo($other);  // true
     *
     * // 10:15:30.000000 vs 10:15:30.999999
     * $this->isEqualTo($other);  // false
     * $this->isEqualTo($other, Precision::Second);  // true
     *
     * // 10:15:30.000000 vs 10:15:59.999999
     * $this->isEqualTo($other, Precision::Second);  // false
     * $this->isEqualTo($other, Precision::Minute);  // true
     *
     * // 10:15:30.000000 vs Minute::of(15)
     * $this->isEqualTo($other);  // true
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isEqualTo(self|TimeUnit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    /**
     * Checks if this time or its unit is not equal to another one.
     *
     * ```
     * // 10:15:30.000000 vs 10:15:30.999999
     * $this->isNotEqualTo($other);  // true
     * $this->isNotEqualTo($other, Precision::Second);  // false
     *
     * // 10:15:30.000000 vs 10:15:59.999999
     * $this->isNotEqualTo($other, Precision::Second);  // true
     * $this->isNotEqualTo($other, Precision::Minute);  // false
     *
     * // 10:15:30.000000 vs Second::of(45)
     * $this->isNotEqualTo($other, Precision::Second);  // true
     * $this->isNotEqualTo($other, Precision::Minute);  // false
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isNotEqualTo(self|TimeUnit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->notEqual();
    }

    /**
     * Checks if this time or its unit is after or equal to another one.
     *
     * ```
     * // 10:15:30.000000 vs 10:15:30.000000
     * $this->isAfterOrEqualTo($other);  // true
     *
     * // 10:15:30.000000 vs 10:15:30.999999
     * $this->isAfterOrEqualTo($other);  // false
     * $this->isAfterOrEqualTo($other, Precision::Second);  // true
     *
     * // 10:15:30.000000 vs 10:15:59.999999
     * $this->isAfterOrEqualTo($other, Precision::Second);  // false
     * $this->isAfterOrEqualTo($other, Precision::Minute);  // true
     *
     * // 10:15:30.000000 vs Minute::of(15)
     * $this->isAfterOrEqualTo($other);  // true
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isAfterOrEqualTo(self|TimeUnit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
    }

    /**
     * Checks if this time or its unit is after another one.
     *
     * ```
     * // 10:15:30.999999 vs 10:15:30.000000
     * $this->isAfter($other);  // true
     * $this->isAfter($other, Precision::Second);  // false
     *
     * // 10:15:59.999999 vs 10:15:30.000000
     * $this->isAfter($other, Precision::Second);  // true
     * $this->isAfter($other, Precision::Minute);  // false
     *
     * // 10:15:59.999999 vs Minute::of(15)
     * $this->isAfter($other);  // false
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function isAfter(self|TimeUnit $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
    }

    /**
     * Compares this time or its unit to another one.
     *
     * ```
     * // 12:15:30.000000 vs 12:15:30.999999
     * $this->compareTo($other)->equal();  // false
     * $this->compareTo($other)->less();   // true
     * $this->compareTo($other, Precision::Second)->equal();  // true
     * $this->compareTo($other, Precision::Second)->less();   // false
     *
     * // 12:15:30.000000 vs Hour::of(12)
     * $this->compareTo($other)->equal();  // true
     * $this->compareTo($other)->less();   // false
     * ```
     *
     * @see \Kronika\Time\Hour – compare to an hour
     * @see \Kronika\Time\Minute – compare to a minute
     * @see \Kronika\Time\Second – compare to a second
     */
    public function compareTo(self|TimeUnit $other, Precision $precision = Precision::Micro): Compared
    {
        return match ($precision) {
            Precision::Micro => $this->instant()->compareTo($this->normalize($other)->instant()),
            Precision::Second => $this->resetMicro()->compareTo($this->normalize($other)->resetMicro()),
            Precision::Minute => $this->resetSecond()->compareTo($this->normalize($other)->resetSecond()),
        };
    }

    /**
     * Returns this time formatted according to a given string
     * and using the global formatter or a given one.
     *
     * Supports {@see \DateTimeInterface::format()} syntax by default.
     * Non-time characters will be printed as-is.
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

    /**
     * Returns an instance of `Instant` with this time.
     */
    public function instant(): Instant
    {
        return $this->remember(static fn(self $time): Instant => Instant::of(
            second: ($time->hour()->value() * 3600) + ($time->minute()->value() * 60) + $time->second()->second(),
            micro: $time->second()->microsecond(),
        ), key: __METHOD__);
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%s:%s:%s', $this->hour(), $this->minute(), $this->second());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['time' => (string)$this];
    }

    /** @internal {@see DateTime::with()} */
    #[\Override]
    public function _withinDateTime(LocalDateTime $datetime, bool $rolling): LocalDateTime
    {
        return $this->at($datetime->date());
    }

    private function normalize(self|TimeUnit $time): self
    {
        return $time instanceof TimeUnit ? $this->with($time) : $time;
    }
}
