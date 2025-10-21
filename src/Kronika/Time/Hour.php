<?php

declare(strict_types=1);

namespace Kronika\Time;

use Kronika\Comparison;
use Kronika\LocalDateTime;
use Kronika\Utils\WeakRefsTrait;
use Kronika\Time;

/**
 * @psalm-type THour=int<0,23>
 * @implements TimeUnit<THour>
 */
final readonly class Hour implements TimeUnit
{
    /** @use WeakRefsTrait<self,THour> */
    use WeakRefsTrait;

    /** @param THour|self $value */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : static::weak(value: $value);
    }

    public static function zero(): self
    {
        static $instance = self::of(0);

        return $instance;
    }

    public static function last(): self
    {
        static $instance = self::of(23);

        return $instance;
    }

    /** @param THour $value */
    private function __construct(
        private int $value,
    ) {
        \assert($value >= 0 && $value < 24);
    }

    #[\Override]
    public function isZero(): bool
    {
        return $this->isEqualTo(self::zero());
    }

    #[\Override]
    public function isLast(): bool
    {
        return $this->isEqualTo(self::last());
    }

    #[\Override]
    public function is(int $value): bool
    {
        return $this->value() === $value;
    }

    /** @return THour */
    public function value(): int
    {
        return $this->value;
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
        return Comparison::compare($this->value(), $other->value());
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%02d', $this->value());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['hour' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinTime(Time $time): Time
    {
        return Time::of($this, $time->minute(), $time->second());
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinTime($dateTime->time()));
    }
}
