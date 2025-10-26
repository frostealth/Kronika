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
 * @psalm-type TSecond=int<0,59>
 * @psalm-type TMicrosecond=int<0,999999>
 * @implements TimeUnit<TSecond>
 */
final readonly class Second implements TimeUnit
{
    /** @use WeakRefsTrait<self,TSecond|TMicrosecond> */
    use WeakRefsTrait;

    /**
     * @param TSecond|self $second
     * @param TMicrosecond|null $micro
     */
    public static function of(int|self $second, ?int $micro = null): self
    {
        if ($second instanceof self) {
            return $micro === null ? $second : self::of($second->second(), $micro);
        }

        return self::weak(second: $second, microsecond: $micro ?? 0);
    }

    public static function zero(): self
    {
        static $instance = self::of(second: 0, micro: 0);

        return $instance;
    }

    public static function last(): self
    {
        static $instance = self::of(second: 59, micro: 999_999);

        return $instance;
    }

    /**
     * @psalm-param TSecond $second
     * @psalm-param TMicrosecond $microsecond
     */
    private function __construct(
        private int $second,
        private int $microsecond,
    ) {
        \assert($second >= 0 && $second < 60);
        \assert($microsecond >= 0 && $microsecond < 1_000_000);
    }

    /** @psalm-return TSecond */
    public function second(): int
    {
        return $this->second;
    }

    /** @psalm-return TMicrosecond */
    public function microsecond(): int
    {
        return $this->microsecond;
    }

    public function value(): float
    {
        return (float)(string)$this;
    }

    public function resetMicro(): self
    {
        return self::of($this->second(), micro: 0);
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

    /** @param TSecond|numeric $value */
    #[\Override]
    public function is(int|string|float $value): bool
    {
        if (\is_int($value)) {
            return $this->second === $value && $this->microsecond === 0;
        }
        \assert(\is_numeric($value));

        [$second, $micro] = \sscanf((string)$value, '%d.%6d');

        return $this->second === $second && $this->microsecond === $micro;
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
        return Comparison::compare((string)$this, (string)$other);
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return \sprintf('%02d.%06d', $this->second, $this->microsecond);
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['second' => (string)$this];
    }

    /** @internal */
    #[\Override]
    public function withinTime(Time $time): Time
    {
        return Time::of($time->hour(), $time->minute(), $this);
    }

    /** @internal */
    #[\Override]
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinTime($dateTime->time()));
    }
}
