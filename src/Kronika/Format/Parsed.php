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

namespace Kronika\Format;

use Kronika\Date\DateUnit;
use Kronika\Date\DayOfMonth;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;

final readonly class Parsed
{
    public function __construct(
        private ?int $year = null,
        private ?int $month = null,
        private ?int $day = null,
        private ?int $hour = null,
        private ?int $minute = null,
        private ?int $second = null,
        private ?int $micro = null,
        private ?string $timezone = null,
    ) {
    }

    /**
     * @template TReturn of DateUnit|TimeUnit|\DateTimeZone
     *
     * @param null|callable(): TReturn $fallback
     * @param non-empty-string $unit
     *
     * @return TReturn
     */
    private static function fall(?callable $fallback, string $unit): mixed
    {
        return ($fallback ?? static fn() => throw new \RuntimeException("Missed {$unit}"))();
    }

    /** @param null|callable(): Year $fallback */
    public function year(?callable $fallback = null): Year
    {
        return \is_int($this->year) ? Year::of($this->year) : self::fall($fallback, 'Year');
    }

    /** @param null|callable(): Month $fallback */
    public function month(?callable $fallback = null): Month
    {
        return \is_int($this->month) ? Month::of($this->month) : self::fall($fallback, 'Month');
    }

    /** @param null|callable(): DayOfMonth $fallback */
    public function day(?callable $fallback = null): DayOfMonth
    {
        return \is_int($this->day) ? DayOfMonth::of($this->day) : self::fall($fallback, 'DayOfMonth');
    }

    /** @param null|callable(): Hour $fallback */
    public function hour(?callable $fallback = null): Hour
    {
        return \is_int($this->hour) ? Hour::of($this->hour) : self::fall($fallback, 'Hour');
    }

    /** @param null|callable(): Minute $fallback */
    public function minute(?callable $fallback = null): Minute
    {
        return \is_int($this->minute) ? Minute::of($this->minute) : self::fall($fallback, 'Minute');
    }

    /** @param null|callable(): Second $fallback */
    public function second(?callable $fallback = null): Second
    {
        if (! \is_int($this->second) && ! \is_int($this->micro)) {
            return self::fall($fallback, 'Second');
        }

        return Second::of(second: $this->second ?? 0, micro: $this->micro ?? 0);
    }

    /** @param null|callable(): \DateTimeZone $fallback */
    public function timezone(?callable $fallback = null): \DateTimeZone
    {
        return \is_string($this->timezone) ? new \DateTimeZone($this->timezone) : self::fall($fallback, 'Timezone');
    }
}
