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

use Kronika\Date\DateUnit;
use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Utils\Comparison;
use Kronika\Utils\WeakRefsTrait;

/**
 * @psalm-import-type TYear from Year
 * @psalm-import-type TMonth from Month
 * @psalm-import-type TDayOfMonth from DayOfMonth
 */
final readonly class Date implements Unit
{
    /** @use WeakRefsTrait<self,Year|TYear|Month|TMonth|DayOfMonth|TDayOfMonth> */
    use WeakRefsTrait;

    /**
     * @psalm-param Year|TYear             $year
     * @psalm-param Month|TMonth           $month
     * @psalm-param DayOfMonth|TDayOfMonth $day
     */
    public static function of(Year|int $year, Month|int $month, DayOfMonth|int $day): self
    {
        return self::weak(year: Year::of($year), month: Month::of($month), day: DayOfMonth::of($day));
    }

    public static function fromDateTime(DateTime|\DateTimeInterface $dateTime): self
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->date();
        }

        [$year, $month, $day] = \sscanf($dateTime->format('Y-m-d'), '%d-%d-%d');

        return self::of($year, $month, $day);
    }

    public static function parse(string $date, string $format = 'Y-m-d'): self
    {
        return self::fromDateTime(\DateTimeImmutable::createFromFormat($format, $date));
    }

    public static function fromTimestamp(float|int $timestamp): self
    {
        return self::fromInstant(Instant::of((int) $timestamp));
    }

    public static function fromInstant(Instant $instant): self
    {
        /** @var \WeakMap<Instant, self> $references */
        static $references = new \WeakMap();
        if (isset($references[$instant])) {
            return $references[$instant];
        }

        ['year' => $year, 'mon' => $month, 'mday' => $day] = \getdate($instant->second());

        return $references[$instant] = self::of($year, $month, $day);
    }

    private function __construct(
        private Year       $year,
        private Month      $month,
        private DayOfMonth $day,
    ){
        \assert($month->containsDay($day, $year));
    }

    public function year(): Year
    {
        return $this->year;
    }

    public function month(): Month
    {
        return $this->month;
    }

    public function day(): DayOfMonth
    {
        return $this->day;
    }

    public function with(DateUnit $unit): self
    {
        return $unit->withinDate($this);
    }

    public function add(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            return self::fromDateTime(LocalDateTime::midnightOf($this)->add($duration));
        }

        return self::fromInstant($this->instant()->add($duration->roundToDays()));
    }

    public function sub(Duration|\DateInterval $duration): self
    {
        if ($duration instanceof \DateInterval) {
            return self::fromDateTime(LocalDateTime::endOfDayOf($this)->sub($duration));
        }

        return self::fromInstant($this->instant()->sub($duration->roundToDays()));
    }

    public function dayOfWeek(): DayOfWeek
    {
        return DayOfWeek::of(\getdate($this->instant()->second())['wday']);
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return $this->at(Time::midnight())->format(
            // @todo: the escaping doesn't work
            \preg_replace('/([^DdjlNSWwzFMmntLoXxYy])/', '\\\\$1', $format),
        );
    }

    public function until(self $end): Duration
    {
        return $this->instant()->until($end->instant());
    }

    public function at(Time $time): LocalDateTime
    {
        return LocalDateTime::of(date: $this, time: $time);
    }

    public function atMidnight(): LocalDateTime
    {
        return $this->at(time: Time::midnight());
    }

    public function atEndOfDay(): LocalDateTime
    {
        return $this->at(time: Time::endOfDay());
    }

    public function toStartOfMonth(): self
    {
        return $this->with(DayOfMonth::of(1));
    }

    public function toEndOfMonth(): self
    {
        return $this->with($this->month()->lastDay($this->year()));
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other)->less();
    }

    public function isBeforeOrEqual(self $other): bool
    {
        return $this->compareTo($other)->lessOrEqual();
    }

    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    public function isNotEqualTo(self $other): bool
    {
        return ! $this->isEqualTo($other);
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other)->greater();
    }

    public function isAfterOrEqual(self $other): bool
    {
        return $this->compareTo($other)->greaterOrEqual();
    }

    public function compareTo(self $other): Comparison
    {
        return $this->instant()->compareTo($other->instant());
    }

    public function instant(): Instant
    {
        return Instant::of(\strtotime((string) $this));
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%04d-%02d-%02d', $this->year()->number(), $this->month()->number(), $this->day()->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['date' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $this->at($dateTime->time());
    }
}
