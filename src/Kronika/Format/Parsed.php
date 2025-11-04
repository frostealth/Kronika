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
use Kronika\Format\Exception\FormatterError;
use Kronika\Format\Parsed\ParsedDate;
use Kronika\Format\Parsed\ParsedTime;
use Kronika\Format\Parsed\ParsedTrait;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;

final readonly class Parsed
{
    use ParsedTrait;

    public function __construct(
        private ?ParsedDate $date = null,
        private ?ParsedTime $time = null,
        private ?string $timezone = null,
    ) {
    }

    /**
     * @param (callable(): Year)|null $fallback
     *
     * @throws FormatterError
     */
    public function year(?callable $fallback = null): Year
    {
        return $this->date?->year($fallback) ?? self::fallback('Year', $fallback)();
    }

    /**
     * @param (callable(): Month)|null $fallback
     *
     * @throws FormatterError
     */
    public function month(?callable $fallback = null): Month
    {
        return $this->date?->month($fallback) ?? self::fallback('Month', $fallback)();
    }

    /**
     * @param (callable(): DayOfMonth)|null $fallback
     *
     * @throws FormatterError
     */
    public function day(?callable $fallback = null): DayOfMonth
    {
        return $this->date?->day($fallback) ?? self::fallback('DayOfMonth', $fallback)();
    }

    /**
     * @param (callable(): Hour)|null $fallback
     *
     * @throws FormatterError
     */
    public function hour(?callable $fallback = null): Hour
    {
        return $this->time?->hour($fallback) ?? self::fallback('Hour', $fallback)();
    }

    /**
     * @param (callable(): Minute)|null $fallback
     *
     * @throws FormatterError
     */
    public function minute(?callable $fallback = null): Minute
    {
        return $this->time?->minute($fallback) ?? self::fallback('Minute', $fallback)();
    }

    /**
     * @param (callable(): Second)|null $fallback
     *
     * @throws FormatterError
     */
    public function second(?callable $fallback = null): Second
    {
        return $this->time?->second($fallback) ?? self::fallback('Second', $fallback)();
    }

    /**
     * @param (callable(): \DateTimeZone)|null $fallback
     *
     * @throws FormatterError
     */
    public function timezone(?callable $fallback = null): \DateTimeZone
    {
        return self::wrap(
            'Timezone',
            fn(string $timezone): \DateTimeZone => new \DateTimeZone($timezone),
            $fallback,
            $this->timezone,
        );
    }

    /**
     * @template TReturn
     *
     * @param (callable(): TReturn)|null $fallback
     *
     * @return callable(): TReturn
     */
    private static function fallback(string $unit, ?callable $fallback): callable
    {
        return $fallback ?? fn() => throw new FormatterError("Failed to parse $unit");
    }
}
