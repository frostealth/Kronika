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

namespace Kronika\Range;

use Kronika\DateTime;
use Kronika\Duration;
use Kronika\Precision;
use Kronika\Range;
use Kronika\Utils\RefTrait;

/**
 * Represents a date-time range with inclusive "since" and exclusive "till".
 *
 * ```
 * $since = ZonedDateTime::parse('2025-12-30 12:15:30 +01:00');
 * $till = LocalDateTime::parse('2025-12-31 20:15:39');
 * $range = DateTimeRange::of($since, $till);
 *
 * $duration = $range->duration();  // 1 day, 8 hours
 * $range->contains($since);  // true
 * $range->contains($till);   // false
 * ```
 *
 * @template-covariant TFirstDateTime of DateTime
 * @template-covariant TSecondDateTime of DateTime
 *
 * @implements Range<TFirstDateTime|TSecondDateTime>
 */
final readonly class DateTimeRange implements Range
{
    use RefTrait;

    /**
     * Obtains an instance of `DateTimeRange`.
     *
     * @template TSinceDateTime of DateTime
     * @template TTillDateTime of DateTime
     *
     * @param TSinceDateTime $since
     * @param TTillDateTime|null $till
     *
     * @return self<TSinceDateTime, ($till is null ? TSinceDateTime : TTillDateTime)>
     *
     * @throws Exception\InvalidDateTimeRange
     */
    public static function of(DateTime $since, ?DateTime $till, Precision $precision = Precision::Second): self
    {
        return self::ref(since: $since, till: $till ?? $since, precision: $precision);
    }

    /**
     * Obtains an instance of `DateTimeRange` where a given duration
     * is simultaneously subtracted from and added to a given date-time.
     *
     * ```
     * $middle = ZonedDateTime::parse('2025-12-30 12:30:00 +01:00');
     * $duration = Duration::of(days: 1, hours: 4, minutes: 30);
     *
     * $range = DateTimeRange::around($middle, $duration);
     * $range->since();  // 2025-12-29 08:00:00
     * $range->till();   // 2025-12-31 17:00:00
     * ```
     *
     * @template TDateTime of DateTime
     *
     * @param TDateTime $middle
     *
     * @return self<TDateTime, TDateTime>
     */
    public static function around(DateTime $middle, Duration $duration, Precision $precision = Precision::Second): self
    {
        return self::of(since: $middle->sub($duration), till: $middle->add($duration), precision: $precision);
    }

    /**
     * @param TFirstDateTime $since
     * @param TSecondDateTime $till
     *
     * @throws Exception\InvalidDateTimeRange
     */
    private function __construct(
        private DateTime $since,
        private DateTime $till,
        private Precision $precision,
    ) {
        $this->assertRange();
    }

    /** @returns TFirstDateTime */
    #[\Override]
    public function since(): DateTime
    {
        return $this->since;
    }

    /** @returns TSecondDateTime */
    #[\Override]
    public function till(): DateTime
    {
        return $this->till;
    }

    public function precision(): Precision
    {
        return $this->precision;
    }

    #[\Override]
    public function isZero(): bool
    {
        return $this->remember(
            static fn(self $that): bool => $that->since->is($that->till, $that->precision),
            key: __METHOD__,
        );
    }

    #[\Override]
    public function duration(): Duration
    {
        return $this->remember(
            static fn(self $that): Duration => $that->since->until($that->till, $that->precision),
            key: __METHOD__,
        );
    }

    /**
     * Checks if this date-time range contains a given another one.
     *
     * @param TFirstDateTime|TSecondDateTime $datetime
     */
    public function contains(DateTime $datetime): bool
    {
        if ($this->isZero()) {
            return $this->since->is($datetime, $this->precision);
        }

        return $this->till->isAfter($datetime, $this->precision)
            && $this->since->isBeforeOrEqualTo($datetime, $this->precision)
        ;
    }

    /** @return \Iterator<non-negative-int, TFirstDateTime> */
    #[\Override]
    public function each(Duration $step): \Iterator
    {
        $step = $step->isZero() ? Duration::ofSecond() : $step;
        $current = $this->since();

        do {
            yield $current;
        } while ($this->contains($current = $current->add($step)));
    }

    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%s – %s', $this->since(), $this->till());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return [
            'since' => (string)$this->since(),
            'till' => (string)$this->till(),
        ];
    }

    /** @throws Exception\InvalidDateTimeRange */
    private function assertRange(): void
    {
        if ($this->since->isAfter($this->till)) {
            throw new Exception\InvalidDateTimeRange("Invalid range: [$this->since] – [$this->till]");
        }
    }
}
