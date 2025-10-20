<?php

declare(strict_types=1);

namespace Kronika\Time;

use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Unit;

/**
 * @template TimeUnitValue of int
 * @psalm-internal Kronika\Time
 * @internal
 */
abstract readonly class TimeUnit implements Unit
{
    abstract protected static function maxValue(): int;

    final public static function of(int|self $value): static
    {
        return $value instanceof self ? $value : self::cached($value);
    }

    /** @param TimeUnitValue $value */
    final protected static function cached(int $value): static
    {
        /** @psalm-var array<non-empty-string, \WeakReference<static>> $references */
        static $references = [];

        // cleanup
        $references = \array_filter($references, static fn(\WeakReference $ref): bool => $ref->get() !== null);

        $key = \sprintf('%s_%d', static::class, $value);
        $instance = ($references[$key] ?? null)?->get();
        if ($instance instanceof static) {
            return $instance;
        }

        $instance = new static($value);
        $references[$key] = \WeakReference::create($instance);

        return $instance;
    }

    /** @param TimeUnitValue $value */
    final private function __construct(
        private int $value,
    ) {
        \assert(0 <= $value && $value <= static::maxValue());
    }

    final public static function zero(): static
    {
        return static::cached(0);
    }

    final public static function last(): static
    {
        return static::cached(static::maxValue());
    }

    final public function isZero(): bool
    {
        return $this->is(0);
    }

    final public function isLast(): bool
    {
        return $this->is(static::maxValue());
    }

    final public function is(int $value): bool
    {
        return $this->value() === $value;
    }

    final public function value(): int
    {
        return $this->value;
    }

    #[\Override]
    final public function __toString(): string
    {
        return \sprintf('%02d', $this->value());
    }

    /** @internal */
    final public function __debugInfo(): array
    {
        $name = \explode('\\', static::class);
        $name = \strtolower(\end($name));

        return [$name => (string) $this];
    }

    /** @internal */
    abstract public function withinTime(Time $time): Time;

    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinTime($dateTime->time()));
    }
}