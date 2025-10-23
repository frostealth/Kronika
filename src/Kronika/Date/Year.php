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

namespace Kronika\Date;

use Kronika\Date;
use Kronika\Duration;
use Kronika\Utils\Comparison;

/**
 * @psalm-type TYear=int<-99999,99999>
 * @implements DateUnit<TYear>
 */
final readonly class Year implements DateUnit
{
    /** @use DateUnitTrait<TYear> */
    use DateUnitTrait;

    /** @psalm-param TYear $value */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::weak(value: $value);
    }

    public function next(): self
    {
        return new self($this->number() + 1);
    }

    public function previous(): self
    {
        return new self($this->number() - 1);
    }

    /** @return int<365>|int<366> */
    public function length(): int
    {
        return $this->isLeap() ? 366 : 365;
    }

    public function duration(): Duration
    {
        return Duration::of(days: $this->length());
    }

    public function isLeap(): bool
    {
        if ($this->number() % 4 !== 0) {
            return false;
        }
        if ($this->number() % 100 !== 0) {
            return true;
        }

        return $this->number() % 400 === 0;
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
        return Comparison::compare($this->number(), $other->number());
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return \sprintf('%04d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['year' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinDate(Date $date): Date
    {
        return Date::of(
            year: $this,
            month: $date->month(),
            day: $date->month()->adjustDay($date->day(), $this),
        );
    }

    #[\Override]
    protected static function minValue(): int
    {
        return -99999;
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 99999;
    }
}
