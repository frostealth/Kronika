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

use Kronika\Utils\Compared;
use Kronika\Utils\Number;
use Kronika\Utils\RefTrait;

/**
 * Represents a duration/interval in days, hours, minutes and seconds.
 * A duration cannot be negative.
 */
final readonly class Duration
{
    use RefTrait;

    /**
     * Obtains an instance of `Duration` from days, hours, minutes and seconds.
     *
     * ```
     * // 1 day, 30 minutes, 45 seconds
     * $duration = Duration::of(days: 1, minutes: 30, seconds: 45);
     * $duration = Duration::of(hours: 24, minutes: 28, seconds: 165);
     * ```
     *
     * @param int $days    non-negative number of days (24 hours).
     * @param int $hours   non-negative number of hours.
     * @param int $minutes non-negative number of minutes.
     * @param int $seconds non-negative number of seconds.
     * @param int $micros  non-negative number of microseconds.
     *
     * @throws Exception\InvalidValue
     *
     * @see self::zero()
     * @see self::ofWeek()
     * @see self::ofDay()
     * @see self::ofHour()
     * @see self::ofMinute()
     * @see self::ofSecond()
     */
    public static function of(int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0, int $micros = 0): self
    {
        self::assertValues(days: $days, hours: $hours, minutes: $minutes, seconds: $seconds, micros: $micros);

        // total duration in seconds
        $hours += $days * 24;
        $minutes += $hours * 60;
        $seconds += $minutes * 60;
        $seconds += \intdiv($micros, 1_000_000);
        $micros = $micros % 1_000_000;

        return self::ref(seconds: $seconds, micro: $micros);
    }

    /**
     * Obtains an instance of `Duration` equaled to zero.
     *
     * ```
     * $duration = Duration::zero();
     * $duration->days();     // 0
     * $duration->hours();    // 0
     * $duration->minutes();  // 0
     * $duration->seconds();  // 0
     * ```
     */
    public static function zero(): self
    {
        static $zero = self::of(seconds: 0);

        return $zero;
    }

    /**
     * Obtains an instance of `Duration` equaled to the whole week (24 * 7 hours).
     */
    public static function ofWeek(): self
    {
        static $week = self::of(days: 7);

        return $week;
    }

    /**
     * Obtains an instance of `Duration` equaled to the whole day (24 hours).
     */
    public static function ofDay(): self
    {
        static $day = self::of(days: 1);

        return $day;
    }

    /**
     * Obtains an instance of `Duration` equaled to the whole hour.
     */
    public static function ofHour(): self
    {
        static $hour = self::of(hours: 1);

        return $hour;
    }

    /**
     * Obtains an instance of `Duration` equaled to the whole minute.
     */
    public static function ofMinute(): self
    {
        static $minute = self::of(minutes: 1);

        return $minute;
    }

    /**
     * Obtains an instance of `Duration` equaled to the whole second.
     */
    public static function ofSecond(): self
    {
        static $second = self::of(seconds: 1);

        return $second;
    }

    /**
     * @param non-negative-int $seconds
     * @param int<0,999999>    $micro
     *
     * @throws Exception\InvalidValue
     */
    private function __construct(
        private int $seconds,
        private int $micro,
    ) {
        self::assertValues(seconds: $seconds, microseconds: $micro);
    }

    /**
     * Returns days of this duration.
     *
     * The day means 24 hours.
     *
     * ```
     * // 2 days, 26 hours, 65 minutes, 100 seconds
     * $this->days();  // 3
     * ```
     *
     * @return non-negative-int
     */
    public function days(): int
    {
        return $this->inDays();
    }

    /**
     * Returns hours of this duration.
     *
     * ```
     * // 2 days, 26 hours, 65 minutes, 100 seconds
     * $this->hours();  // 3
     * ```
     *
     * @return int<0,23>
     */
    public function hours(): int
    {
        return $this->inHours() - ($this->inDays() * 24);
    }

    /**
     * Returns minutes of this duration.
     *
     * ```
     * // 2 days, 26 hours, 65 minutes, 100 seconds
     * $this->minutes();  // 6
     * ```
     *
     * @return int<0,59>
     */
    public function minutes(): int
    {
        return $this->inMinutes() - ($this->inHours() * 60);
    }

    /**
     * Returns seconds of this duration.
     *
     * ```
     * // 2 days, 26 hours, 65 minutes, 100 seconds
     * $this->seconds();  // 40
     * ```
     *
     * @return int<0,59>
     */
    public function seconds(): int
    {
        return $this->inSeconds() - ($this->inMinutes() * 60);
    }

    /**
     * Returns microseconds of this duration.
     *
     * ```
     * // 2 days, 26 hours, 65 minutes, 100 seconds, 1234 microseconds
     * $this->microseconds();  // 1234
     * ```
     *
     * @return int<0,999999>
     */
    public function microseconds(): int
    {
        return $this->micro;
    }

    /**
     * Returns a rounded amount of days in this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $this->inDays();  // 2
     * $this->inDays(\RoundingMode::AwayFromZero);  // 3
     * ```
     *
     * @return non-negative-int
     */
    public function inDays(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inHours(mode: $mode) / 24, mode: $mode);
    }

    /**
     * Returns a rounded amount of hours in this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $this->inHours();  // 60
     * $this->inHours(\RoundingMode::AwayFromZero);  // 61
     * ```
     *
     * @return non-negative-int
     */
    public function inHours(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inMinutes(mode: $mode) / 60, mode: $mode);
    }

    /**
     * Returns a rounded amount of minutes in this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $this->inMinutes();  // 3630
     * $this->inMinutes(\RoundingMode::AwayFromZero);  // 3631
     * ```
     *
     * @return non-negative-int
     */
    public function inMinutes(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inSeconds(mode: $mode) / 60, mode: $mode);
    }

    /**
     * Returns an amount of seconds in this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 seconds, 3 microseconds
     * $this->inSeconds();  // 217845
     * $this->inSeconds(\RoundingMode::AwayFromZero);  // 217846
     * ```
     *
     * @return non-negative-int
     */
    public function inSeconds(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        if ($mode === \RoundingMode::TowardsZero) {
            return $this->seconds;
        }

        return (int)\round($this->number()->toFloat(), mode: $mode);
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return $this->toDateInterval()->format($format);
    }

    /**
     * Adds other durations to this one.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $duration = $this->add(
     *     Duration::of(days: 1),
     *     Duration::of(hours: 3),
     * );
     * $duration->days();     // 3
     * $duration->hours();    // 15
     * $duration->minutes();  // 30
     * $duration->seconds();  // 45
     * ```
     */
    public function add(self ...$others): self
    {
        return self::ofNumber($this->number()->add(...\array_map(
            static fn(self $other): Number => $other->number(),
            $others,
        )));
    }

    /**
     * Subtracts other durations from this one.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $duration = $this->sub(
     *     Duration::of(days: 1),
     *     Duration::of(hours: 3),
     * );
     * $duration->days();     // 1
     * $duration->hours();    // 9
     * $duration->minutes();  // 30
     * $duration->seconds();  // 45
     * ```
     */
    public function sub(self ...$others): self
    {
        $result = $this->number()->sub(...\array_map(
            static fn(self $other): Number => $other->number(),
            $others,
        ));

        return $result->isPositive() ? self::ofNumber($result) : self::zero();
    }

    /**
     * Returns an instance of `Duration` with rounded amount of days in this duration.
     *
     * @see self::inDays()
     */
    public function roundToDays(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(days: $this->inDays(mode: $mode));
    }

    /**
     * Returns an instance of `Duration` with rounded amount of hours in this duration.
     *
     * @see self::inHours()
     */
    public function roundToHours(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(hours: $this->inHours(mode: $mode));
    }

    /**
     * Returns an instance of `Duration` with rounded amount of minutes in this duration.
     *
     * @see self::inMinutes()
     */
    public function roundToMinutes(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(minutes: $this->inMinutes(mode: $mode));
    }

    /**
     * Returns an instance of `Duration` with rounded amount of seconds in this duration.
     *
     * @see self::inSeconds()
     */
    public function roundToSeconds(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(seconds: $this->inSeconds(mode: $mode));
    }

    /**
     * Returns a duration with dropped (truncated) days.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $duration = $this->dropToHours();
     * $duration->days();     // 0
     * $duration->hours();    // 12
     * $duration->minutes();  // 30
     * $duration->seconds();  // 45
     * ```
     */
    public function dropToHours(): self
    {
        return $this->sub($this->roundToDays());
    }

    /**
     * Returns a duration with dropped (truncated) days and hours.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $duration = $this->dropToMinutes();
     * $duration->days();     // 0
     * $duration->hours();    // 0
     * $duration->minutes();  // 30
     * $duration->seconds();  // 45
     * ```
     */
    public function dropToMinutes(): self
    {
        return $this->sub($this->roundToHours());
    }

    /**
     * Returns a duration with dropped (truncated) days, hours and minutes.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $duration = $this->dropToSeconds();
     * $duration->days();     // 0
     * $duration->hours();    // 0
     * $duration->minutes();  // 0
     * $duration->seconds();  // 45
     * ```
     */
    public function dropToSeconds(): self
    {
        return $this->sub($this->roundToMinutes());
    }

    /**
     * Checks if this duration equals to zero.
     *
     * ```
     * Duration::zero()->isZero();  // true
     * ```
     *
     * @see self::zero()
     */
    public function isZero(): bool
    {
        return $this->is(self::zero());
    }

    /**
     * Checks if this duration is equal to another one.
     *
     * ```
     * // 2 days
     * $this->is(Duration::of(days: 2));  // true
     * $this->is(Duration::zero());       // false
     * $this->is(Duration::of(days: 3));  // false
     * ```
     */
    public function is(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    /**
     * Checks if this duration is not equal to another one.
     *
     * ```
     * // 2 days
     * $this->isNot(Duration::of(days: 2));  // false
     * $this->isNot(Duration::zero());       // true
     * $this->isNot(Duration::of(days: 3));  // true
     * ```
     */
    public function isNot(self $other): bool
    {
        return $this->compareTo($other)->notEqual();
    }

    /**
     * Checks if this duration is less than another one.
     *
     * ```
     * // 2 days
     * $this->isLessThan(Duration::of(days: 2));  // false
     * $this->isLessThan(Duration::zero());       // false
     * $this->isLessThan(Duration::of(days: 3));  // true
     * ```
     */
    public function isLessThan(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    /**
     * Checks if this duration is less than or equal to another one.
     *
     * ```
     * // 2 days
     * $this->isLessThanOrEqualTo(Duration::of(days: 2));  // true
     * $this->isLessThanOrEqualTo(Duration::zero());       // false
     * $this->isLessThanOrEqualTo(Duration::of(days: 3));  // true
     * ```
     */
    public function isLessThanOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    /**
     * Checks if this duration is greater than or equal to another one.
     *
     * ```
     * // 2 days
     * $this->isGreaterThanOrEqualTo(Duration::of(days: 2));  // false
     * $this->isGreaterThanOrEqualTo(Duration::zero());       // true
     * $this->isGreaterThanOrEqualTo(Duration::of(days: 3));  // false
     * ```
     */
    public function isGreaterThanOrEqualTo(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    /**
     * Checks if this duration is greater than another one.
     *
     * ```
     * // 2 days
     * $this->isGreaterThan(Duration::of(days: 2));  // false
     * $this->isGreaterThan(Duration::zero());       // true
     * $this->isGreaterThan(Duration::of(days: 3));  // false
     * ```
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    /**
     * Compares this duration to another one.
     *
     * ```
     * // 2 days 10 hour
     * $this->compareTo(Duration::of(days: 3))->less();     // true
     * $this->compareTo(Duration::of(days: 2))->greater();  // true
     * $this->compareTo(Duration::of(hours: 10))->equal();  // false
     * ```
     */
    public function compareTo(self $other): Compared
    {
        return Compared::of($this <=> $other);
    }

    /**
     * Obtains an instance of `\DateInterval` from this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $this->toDateInterval();  // \DateInterval('P2DT12H30M45S')
     * ```
     */
    public function toDateInterval(): \DateInterval
    {
        return \DateInterval::createFromDateString((string)$this);
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf(
            '%02d days, %02d hours, %02d minutes, %02d seconds, %06d microseconds',
            $this->days(),
            $this->hours(),
            $this->minutes(),
            $this->seconds(),
            $this->microseconds(),
        );
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return [
            'days' => $this->days(),
            'hours' => $this->hours(),
            'minutes' => $this->minutes(),
            'seconds' => $this->seconds(),
            'microseconds' => $this->microseconds(),
            'inSeconds' => $this->inSeconds(),
        ];
    }

    /**
     * @throws Exception\InvalidValue
     *
     * @psalm-assert non-negative-int ...$values
     */
    private static function assertValues(int ...$values): void
    {
        foreach ($values as $name => $value) {
            if ($value < 0) {
                throw new Exception\InvalidValue(
                    \sprintf('%s cannot be negative, got [%d]', \ucfirst($name), $value),
                );
            }
        }
    }

    /** @throws Exception\InvalidValue */
    private static function ofNumber(Number $number): self
    {
        return self::ref(seconds: $number->integer(), micro: $number->fraction());
    }

    private function number(): Number
    {
        return $this->remember(
            static fn(self $that): Number => Number::of($that->seconds, $that->micro),
            key: __METHOD__,
        );
    }
}
