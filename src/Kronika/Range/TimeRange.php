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

use Kronika\Duration;
use Kronika\Precision;
use Kronika\Range;
use Kronika\Time;
use Kronika\Utils\RefTrait;

/**
 * Represents a time range with inclusive "since" and exclusive "till".
 *
 * ```
 * $since = Time::of(12, 15, 30);
 * $till = Time::of(20, 15, 30);
 * $range = TimeRange::of($since, $till);
 *
 * $duration = $range->duration();  // 8 hours
 * $range->contains($since);  // true
 * $range->contains($till);   // false
 * ```
 *
 * @implements Range<Time>
 */
final readonly class TimeRange implements Range
{
    use RefTrait;

    /**
     * Obtains an instance of `TimeRange`.
     *
     * @param Precision $precision {@DEPRECATED since 0.2.5}
     *
     * @throws Exception\InvalidTimeRange
     */
    public static function of(Time $since, ?Time $till, Precision $precision = Precision::Second): self
    {
        return self::ref(since: $since, till: $till ?? $since, precision: $precision);
    }

    /** @throws Exception\InvalidTimeRange */
    private function __construct(
        private Time $since,
        private Time $till,
        private Precision $precision,
    ) {
        $this->assertRange();
    }

    #[\Override]
    public function since(): Time
    {
        return $this->since;
    }

    #[\Override]
    public function till(): Time
    {
        return $this->till;
    }

    /** @deprecated */
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
     * Checks if this time range contains a given another one.
     */
    public function contains(Time $time): bool
    {
        if ($this->isZero()) {
            return $this->since->is($time, $this->precision);
        }

        return $this->till->isAfter($time, $this->precision)
            && $this->since->isBeforeOrEqualTo($time, $this->precision)
        ;
    }

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

    /** @throws Exception\InvalidTimeRange */
    private function assertRange(): void
    {
        if ($this->since->isAfter($this->till)) {
            throw new Exception\InvalidTimeRange("Invalid time range: [$this->since] – [$this->till]");
        }
    }
}
