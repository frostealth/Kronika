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

namespace Kronika\Time;

use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Utils\Comparison;
use Kronika\Utils\WeakRefsTrait;

/**
 * @psalm-type TMinute=int<0,59>
 * @implements TimeUnit<TMinute>
 */
final readonly class Minute implements TimeUnit
{
    /** @use WeakRefsTrait<self,TMinute> */
    use WeakRefsTrait;

    /** @param TMinute|self $value */
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
        static $instance = self::of(59);

        return $instance;
    }

    /** @param TMinute $value */
    private function __construct(
        private int $value,
    ) {
        \assert($value >= 0 && $value < 60);
    }

    /** @return TMinute */
    public function value(): int
    {
        return $this->value;
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
        return ['minute' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinTime(Time $time): Time
    {
        return Time::of($time->hour(), $this, $time->second());
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinTime($dateTime->time()));
    }
}
