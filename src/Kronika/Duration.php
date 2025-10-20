<?php

declare(strict_types=1);

namespace Kronika;

final readonly class Duration
{
    public static function zero(): self
    {
        /** @var null|self $instance */
        static $instance;

        return $instance ??= new self(seconds: 0);
    }

    /**
     * @param non-negative-int $days
     * @param non-negative-int $hours
     * @param non-negative-int $minutes
     * @param non-negative-int $seconds
     */
    public static function of(int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        // total duration in seconds
        $hours += $days * 24;
        $minutes += $hours * 60;
        $seconds += $minutes * 60;

        return 0 === $seconds ? self::zero() : new self(seconds: $seconds);
    }

    public static function between(\DateTimeInterface $from, \DateTimeInterface $to): self
    {
        if ($from >= $to) {
            return self::zero();
        }

        return self::of(seconds: $to->getTimestamp() - $from->getTimestamp());
    }

    /** @param non-negative-int $seconds */
    private function __construct(
        private int $seconds,
    ) {
        \assert(0 <= $seconds, 'Duration cannot be negative');
    }

    /** @return non-negative-int */
    public function inDays(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inHours(mode: $mode) / 24, mode: $mode);
    }

    /** @return non-negative-int */
    public function inHours(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inMinutes(mode: $mode) / 60, mode: $mode);
    }

    /** @return non-negative-int */
    public function inMinutes(\RoundingMode $mode = \RoundingMode::TowardsZero): int
    {
        return (int)\round($this->inSeconds() / 60, mode: $mode);
    }

    /** @return non-negative-int */
    public function inSeconds(): int
    {
        return $this->seconds;
    }

    /** @return non-negative-int */
    public function days(): int
    {
        return $this->inDays();
    }

    /** @return int<0,23> */
    public function hours(): int
    {
        return $this->inHours() - ($this->inDays() * 24);
    }

    /** @return int<0,59> */
    public function minutes(): int
    {
        return $this->inMinutes() - ($this->inHours() * 60);
    }

    /** @return int<0,59> */
    public function seconds(): int
    {
        return $this->inSeconds() - ($this->inMinutes() * 60);
    }

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string
    {
        return $this->toDateInterval()->format(
            \preg_replace('/%([^DdHhIiSs])//', '$1', $format),
        );
    }

    public function add(self ...$others): self
    {
        return self::of(seconds: \array_reduce(
            $others,
            static fn(int $total, self $other): int => $total + $other->seconds,
            initial: $this->seconds,
        ));
    }

    public function sub(self ...$others): self
    {
        $seconds = \array_reduce(
            $others,
            static fn(int $total, self $other): int => $total - $other->seconds,
            initial: $this->seconds,
        );

        return 0 < $seconds ? self::of(seconds: $seconds) : self::zero();
    }

    public function isZero(): bool
    {
        return $this->isEqualTo(self::zero());
    }

    public function isEqualTo(self $other): bool
    {
        return $this->compareTo($other)->equal();
    }

    public function compareTo(self $other): Comparison
    {
        return Comparison::compare($this->seconds, $other->seconds);
    }

    public function roundToDays(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(days: $this->inDays(mode: $mode));
    }

    public function roundToHours(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(hours: $this->inHours(mode: $mode));
    }

    public function roundToMinutes(\RoundingMode $mode = \RoundingMode::TowardsZero): self
    {
        return self::of(minutes: $this->inMinutes(mode: $mode));
    }

    public function toDateInterval(): \DateInterval
    {
        return new \DateInterval("P{$this->days()}DT{$this->hours()}H{$this->minutes()}M{$this->seconds()}S");
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%02d days, %02d hours, %02d minutes, %02d seconds', $this->days(), $this->hours(), $this->minutes(), $this->seconds());
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
}
