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

use Kronika\Date;
use Kronika\Duration;
use Kronika\Range;
use Kronika\Utils\RefTrait;

/**
 * Represents a date range with inclusive "since" and exclusive "till".
 *
 * ```
 * $since = Date::of(2025, 12, 01);
 * $till = Date::of(2025, 12, 31);
 * $range = DateRange::of($since, $till);
 *
 * $duration = $range->duration();  // 30 days
 * $range->contains($since);  // true
 * $range->contains($till);   // false
 * ```
 *
 * @implements Range<Date>
 */
final readonly class DateRange implements Range
{
    use RefTrait;

    /**
     * Obtains an instance of `DateRange`.
     *
     * @throws Exception\InvalidDateRange
     */
    public static function of(Date $since, ?Date $till): self
    {
        return self::ref(since: $since, till: $till ?? $since);
    }

    /**
     * Obtains an instance of `DateRange` where a given duration
     * is simultaneously subtracted from and added to a given date.
     *
     * ```
     * $middle = Date::of(2025, 12, 15);
     * $duration = Duration::of(days: 5);
     *
     * $range = DateRange::around($middle, $duration);
     * $range->since();  // 2025-12-10
     * $range->till();   // 2025-12-20
     * ```
     */
    public static function around(Date $middle, Duration $duration): self
    {
        return self::of(since: $middle->sub($duration), till: $middle->add($duration));
    }

    /** @throws Exception\InvalidDateRange */
    private function __construct(
        private Date $since,
        private Date $till,
    ) {
        $this->assertRange();
    }

    #[\Override]
    public function since(): Date
    {
        return $this->since;
    }

    #[\Override]
    public function till(): Date
    {
        return $this->till;
    }

    #[\Override]
    public function isZero(): bool
    {
        return $this->remember(
            static fn(self $that): bool => $that->since->isEqualTo($that->till),
            key: __METHOD__,
        );
    }

    #[\Override]
    public function duration(): Duration
    {
        return $this->remember(
            static fn(self $that): Duration => $that->since->until($that->till),
            key: __METHOD__,
        );
    }

    /**
     * Checks if this date range contains a given another one.
     */
    public function contains(Date $date): bool
    {
        if ($this->isZero()) {
            return $this->since->isEqualTo($date);
        }

        return $this->till->isAfter($date) && $this->since->isBeforeOrEqualTo($date);
    }

    #[\Override]
    public function each(Duration $step): \Iterator
    {
        $step = $step->isLessThan(Duration::ofDay()) ? Duration::ofDay() : $step;
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

    /** @throws Exception\InvalidDateRange */
    private function assertRange(): void
    {
        if ($this->since->isAfter($this->till)) {
            throw new Exception\InvalidDateRange("Invalid range: [$this->since] – [$this->till]");
        }
    }
}
