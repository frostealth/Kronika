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

use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;
use Kronika\Utils\Comparison;
use Kronika\Utils\WeakRefsTrait;

/**
 * Represents a time.
 *
 * @psalm-import-type THour from Hour
 * @psalm-import-type TMinute from Minute
 * @psalm-import-type TSecond from Second
 */
final readonly class Time implements Unit
{
    /** @use WeakRefsTrait<self,Hour|THour|Minute|TMinute|Second|TSecond> */
    use WeakRefsTrait;

    /**
     * Obtains an instance of Time from an hour, minute and second.
     *
     * @example
     * ```
     * // 10:30:45
     * $time = Time::of(hour: 10, minute: 30, second: 45);
     *
     * // 11:45.55.000999
     * $time = Time::of(hour: 11, minute: 45, second: Second::of(55, 999));
     * ```
     *
     * @psalm-param Hour|THour     $hour
     * @psalm-param Minute|TMinute $minute
     * @psalm-param Second|TSecond $second
     */
    public static function of(Hour|int $hour, Minute|int $minute, Second|int $second = 0): self
    {
        return self::weak(hour: Hour::of($hour), minute: Minute::of($minute), second: Second::of($second));
    }

    /**
     * Obtains an instance of Time at the start of the day ("00:00:00.000000").
     */
    public static function midnight(): self
    {
        static $instance = self::of(Hour::zero(), Minute::zero(), Second::zero());

        return $instance;
    }

    /**
     * Obtains an instance of Time at the middle of the day ("12:00:00.000000").
     */
    public static function midday(): self
    {
        static $instance = self::of(Hour::of(12), Minute::zero(), Second::zero());

        return $instance;
    }

    /**
     * Obtains an instance of Time at the end of the day ("23:59:59.999999").
     */
    public static function endOfDay(): self
    {
        static $instance = self::of(Hour::last(), Minute::last(), Second::last());

        return $instance;
    }

    /**
     * Obtain an instance of Time from a date-time.
     */
    public static function ofDateTime(DateTime|\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->time();
        }

        [$hour, $minute, $second, $micro] = \sscanf($dateTime->format('H:i:s.u'), '%d:%d:%d.%d');

        return self::of($hour, $minute, Second::of($second, $micro));
    }

    /**
     * Obtain an instance of Time from a timestamp.
     */
    public static function ofTimestamp(float|int $timestamp): self
    {
        return self::ofInstant(Instant::ofValue($timestamp));
    }

    /**
     * Obtain an instance of Time from a "Kronika\Instant".
     */
    public static function ofInstant(Instant $instant): self
    {
        /** @var \WeakMap<Instant, self> $references */
        static $references = new \WeakMap();
        if (isset($references[$instant])) {
            return $references[$instant];
        }

        ['hours' => $hour, 'minutes' => $minute, 'seconds' => $second] = \getdate($instant->second());

        return $references[$instant] = self::of($hour, $minute, Second::of($second, $instant->microsecond()));
    }

    /**
     * Obtain an instance of Time from a format.
     *
     * @param non-empty-string $format
     * @param non-empty-string $time
     */
    public static function ofFormat(string $format, string $time): self
    {
        return self::ofDateTime(\DateTimeImmutable::createFromFormat($format, $time));
    }

    private function __construct(
        private Hour $hour,
        private Minute $minute,
        private Second $second,
    ){
    }

    public function hour(): Hour
    {
        return $this->hour;
    }

    public function minute(): Minute
    {
        return $this->minute;
    }

    public function second(): Second
    {
        return $this->second;
    }

    public function with(TimeUnit $unit): self
    {
        return $unit->withinTime($this);
    }

    public function at(Date $date): LocalDateTime
    {
        return $date->at($this);
    }

    public function resetMicro(): self
    {
        return $this->with($this->second()->resetMicro());
    }

    public function resetSecond(): self
    {
        return $this->with(Second::zero());
    }

    public function add(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            $duration = Duration::of(hours: $duration->h, minutes: $duration->i, seconds: $duration->s);
        }

        return self::ofInstant($this->instant()->add($duration->dropToHours()));
    }

    public function sub(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            $duration = Duration::of(hours: $duration->h, minutes: $duration->i, seconds: $duration->s);
        }

        return self::ofInstant($this->instant()->sub($duration->dropToHours()));
    }

    public function until(self $end): Duration
    {
        return $this->instant()->until($end->instant());
    }

    public function diff(self $other): Duration
    {
        return $this->instant()->diff($other->instant());
    }

    public function isMidnight(Precision $precision = Precision::Micro): bool
    {
        return $this->isEqualTo(self::midnight(), $precision);
    }

    public function isMidday(Precision $precision = Precision::Micro): bool
    {
        return $this->isEqualTo(self::midday(), $precision);
    }

    public function isEndOfDay(Precision $precision = Precision::Micro): bool
    {
        return $this->isEqualTo(self::endOfDay(), $precision);
    }

    public function isBefore(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->less();
    }

    public function isBeforeOrEqual(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->lessOrEqual();
    }

    public function isEqualTo(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->equal();
    }

    public function isNotEqualTo(self $other, Precision $precision = Precision::Micro): bool
    {
        return ! $this->isEqualTo($other, $precision);
    }

    public function isAfter(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greater();
    }

    public function isAfterOrEqual(self $other, Precision $precision = Precision::Micro): bool
    {
        return $this->compareTo($other, $precision)->greaterOrEqual();
    }

    public function compareTo(self $other, Precision $precision = Precision::Micro): Comparison
    {
        return match ($precision) {
            Precision::Micro => $this->instant()->compareTo($other->instant()),
            Precision::Second => $this->resetMicro()->compareTo($other->resetMicro()),
            Precision::Minute => $this->resetSecond()->compareTo($other->resetSecond()),
        };
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return \DateTimeImmutable::createFromTimestamp($this->instant()->value())->format(
            // @todo: the escaping doesn't work
            \preg_replace('/([^AaBGgHisu])/', '\\\\$1', $format),
        );
    }

    public function instant(): Instant
    {
        return Instant::of(
            second:($this->hour()->value() * 3600) + ($this->minute()->value() * 60) + $this->second()->second(),
            micro: $this->second()->microsecond(),
        );
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%s:%s:%s', $this->hour(), $this->minute(), $this->second());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['time' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $this->at($dateTime->date());
    }
}
