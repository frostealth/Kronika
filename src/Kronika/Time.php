<?php

declare(strict_types=1);

namespace Kronika;

use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;

/**
 * @psalm-import-type HourValue from Hour
 * @psalm-import-type MinuteValue from Minute
 * @psalm-import-type SecondValue from Second
 */
final readonly class Time implements Unit
{
    private function __construct(
        private Hour $hour,
        private Minute $minute,
        private Second $second,
    ){
    }

    public static function midnight(): self
    {
        static $instance;

        return $instance ??= new self(Hour::zero(), Minute::zero(), Second::zero());
    }

    public static function noon(): self
    {
        static $instance;

        return $instance ??= new self(Hour::of(12), Minute::zero(), Second::zero());
    }

    public static function endOfDay(): self
    {
        static $instance;

        return $instance ??= new self(Hour::last(), Minute::last(), Second::last());
    }

    /**
     * @psalm-param Hour|HourValue     $hour
     * @psalm-param Minute|MinuteValue $minute
     * @psalm-param Second|SecondValue $second
     */
    public static function of(Hour|int $hour, Minute|int $minute, Second|int $second = 0): self
    {
        $hour = Hour::of($hour);
        $minute = Minute::of($minute);
        $second = Second::of($second);

        if ($hour->isZero() && $minute->isZero() && $second->isZero()) {
            return self::midnight();
        }
        if ($hour->is(12) && $minute->isZero() && $second->isZero()) {
            return self::noon();
        }
        if ($hour->isLast() && $minute->isLast() && $second->isLast()) {
            return self::endOfDay();
        }

        return new self(hour: $hour, minute: $minute, second: $second);
    }

    public static function ofDateTime(\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->time();
        }

        [$hour, $minute, $second] = \explode(':', $dateTime->format('H:i:s'));

        return self::of((int) $hour, (int) $minute, (int) $second);
    }

    public static function ofTimestamp(int $timestamp): self
    {
        return self::ofInstant(Instant::of($timestamp));
    }

    public static function ofInstant(Instant $instant): self
    {
        /** @var \WeakMap<Instant, self> $references */
        static $references = new \WeakMap();
        if (isset($references[$instant])) {
            return $references[$instant];
        }

        ['hours' => $hour, 'minutes' => $minute, 'seconds' => $second] = \getdate($instant->second());

        return $references[$instant] = self::of($hour, $minute, $second);
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
        $duration = $duration->sub($duration->roundToDays());  // @todo: move into "Duration"

        return self::ofInstant($this->instant()->add($duration));
    }

    public function sub(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            $duration = Duration::of(hours: $duration->h, minutes: $duration->i, seconds: $duration->s);
        }
        $duration = $duration->sub($duration->roundToDays());

        return self::ofInstant($this->instant()->sub($duration));
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
     * Форматирование даты.
     *
     * Шаблон соответствует {@see \DateTimeInterface::format()},
     * но поддерживаются только часы, минуты и секунды.
     *
     * В строке format распознаются следующие символы:
     * – A - AM/PM ("до полудня"/"после полудня") в верхнем регистре.
     * – a - am/pm ("до полудня"/"после полудня") в нижнем регистре.
     * – B - Swatch internet time (формат времени компании Swatch: число от 000 до 999).
     * – G - Часы в 24-часовом формате без ведущего нуля (от 0 до 23).
     * – g - Часы в 12-часовом формате без ведущего нуля (от 1 до 12).
     * – H - Часы в 24-часовом формате с ведущим нулем (от 00 до 23).
     * – h - Часы в 12-часовом формате с ведущим нулем (от 01 до 12).
     * – i - Минуты с ведущим нулем (от 00 до 59).
     * – s - Секунды с ведущим нулем (от 00 до 59).
     *
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
        return Instant::of(($this->hour()->value() * 3600) + ($this->minute()->value() * 60) + $this->second()->value());
    }

    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $this->at($dateTime->date());
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf(
            '%02d:%02d:%02d',
            $this->hour()->value(),
            $this->minute()->value(),
            $this->second()->value(),
        );
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['time' => (string) $this];
    }
}
