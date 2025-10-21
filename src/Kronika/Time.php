<?php

declare(strict_types=1);

namespace Kronika;

use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;
use Kronika\Utils\WeakRefsTrait;

/**
 * @psalm-import-type THour from Hour
 * @psalm-import-type TMinute from Minute
 * @psalm-import-type TSecond from Second
 */
final readonly class Time implements Unit
{
    /** @use WeakRefsTrait<self,Hour|THour|Minute|TMinute|Second|TSecond> */
    use WeakRefsTrait;

    /**
     * @psalm-param Hour|THour     $hour
     * @psalm-param Minute|TMinute $minute
     * @psalm-param Second|TSecond $second
     */
    public static function of(Hour|int $hour, Minute|int $minute, Second|int $second = 0): self
    {
        return self::weak(hour: Hour::of($hour), minute: Minute::of($minute), second: Second::of($second));
    }

    public static function midnight(): self
    {
        static $instance = self::of(Hour::zero(), Minute::zero(), Second::zero());

        return $instance;
    }

    public static function noon(): self
    {
        static $instance = self::of(Hour::of(12), Minute::zero(), Second::zero());

        return $instance;
    }

    public static function endOfDay(): self
    {
        static $instance = self::of(Hour::last(), Minute::last(), Second::last());

        return $instance;
    }

    public static function ofDateTime(\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->time();
        }

        [$hour, $minute, $second, $micro] = \sscanf($dateTime->format('H:i:s.u'), '%d:%d:%d.%d');

        return self::of($hour, $minute, Second::of($second, $micro));
    }

    public static function ofTimestamp(float|int $timestamp): self
    {
        return self::ofInstant(Instant::ofValue($timestamp));
    }

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
     * @param non-empty-string $time
     * @param non-empty-string $format
     *
     * @return self
     */
    public static function ofFormat(string $time, string $format = 'H:i:s'): self
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

    public function diff(self $other): Duration
    {
        return $this->instant()->diff($other->instant());
    }

    public function until(self $end): Duration
    {
        return $this->instant()->until($end->instant());
    }

    public function truncateSeconds(): self
    {
        return $this->with(Second::zero());
    }

    public function isMidnight(): bool
    {
        return $this->isEqualTo(self::midnight());
    }

    public function isNoon(): bool
    {
        return $this->isEqualTo(self::noon());
    }

    public function isEndOfDay(): bool
    {
        return $this->isEqualTo(self::endOfDay());
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

    public function at(Date $date): LocalDateTime
    {
        return $date->at($this);
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return \DateTimeImmutable::createFromTimestamp($this->instant()->second())->format(
            // @todo: the escaping doesn't work
            \preg_replace('/([^AaBGgHis])/', '\\\\$1', $format),
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
