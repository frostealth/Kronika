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
     * @param non-negative-int $days
     * @param non-negative-int $hours
     * @param non-negative-int $minutes
     * @param non-negative-int $seconds
     *
     * @throws Exception\InvalidValue
     */
    public static function of(int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        self::assertValues(days: $days, hours: $hours, minutes: $minutes, seconds: $seconds);

        // total duration in seconds
        $hours += $days * 24;
        $minutes += $hours * 60;
        $seconds += $minutes * 60;

        return self::ref(seconds: $seconds);
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
     * Obtains an instance of `Duration` equaled to the whole week.
     */
    public static function ofWeek(): self
    {
        static $week = self::of(days: 7);

        return $week;
    }

    /**
     * Obtains an instance of `Duration` equaled to the whole day.
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
     *
     * @throws Exception\InvalidValue
     */
    private function __construct(
        private int $seconds,
    ) {
        self::assertValues(duration: $seconds);
    }

    /**
     * Returns days of this duration.
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
     * Returns a rounded amount of days in this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $this->inDays();  // 2
     * $this->inDays(\RoundingMode::HalfAwayFromZero);  // 3
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
     * $this->inHours(\RoundingMode::HalfAwayFromZero);  // 61
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
     * $this->inMinutes(\RoundingMode::HalfAwayFromZero);  // 3631
     * ```
     *
     * @return non-negative-int
     */
    public function inMinutes(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inSeconds() / 60, mode: $mode);
    }

    /**
     * Returns an amount of seconds in this duration.
     *
     * ```
     * // 2 days, 12 hours, 30 minutes, 45 second
     * $this->inSeconds();  // 217845
     * ```
     *
     * @return non-negative-int
     */
    public function inSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return $this->toDateInterval()->format(
            \preg_replace('/%([^DdHhIiSs])/', '$1', $format),
        );
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
        return self::of(seconds: \array_reduce(
            $others,
            static fn(int $total, self $other): int => $total + $other->seconds,
            initial: $this->seconds,
        ));
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
        $seconds = \array_reduce(
            $others,
            static fn(int $total, self $other): int => $total - $other->seconds,
            initial: $this->seconds,
        );

        return 0 < $seconds ? self::of(seconds: $seconds) : self::zero();
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

    /** @deprecated {@see self::is()} */
    public function isEqualTo(self $other): bool
    {
        return $this->is($other);
    }

    /** @deprecated {@see self::isNot()} */
    public function isNotEqualTo(self $other): bool
    {
        return $this->isNot($other);
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
        return new \DateInterval("P{$this->days()}DT{$this->hours()}H{$this->minutes()}M{$this->seconds()}S");
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf(
            '%02d days, %02d hours, %02d minutes, %02d seconds',
            $this->days(),
            $this->hours(),
            $this->minutes(),
            $this->seconds(),
        );
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return [
            'days' => \sprintf('%02d', $this->days()),
            'hours' => \sprintf('%02d', $this->hours()),
            'minutes' => \sprintf('%02d', $this->minutes()),
            'seconds' => \sprintf('%02d', $this->seconds()),
            'inSeconds' => \sprintf('%02d', $this->inSeconds()),
        ];
    }

    /** @throws Exception\InvalidValue */
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
}
