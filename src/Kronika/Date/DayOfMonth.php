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
use Kronika\Utils\Comparison;

/**
 * @psalm-type TDayOfMonth=int<1,31>
 * @implements DateUnit<TDayOfMonth>
 */
final readonly class DayOfMonth implements DateUnit
{
    /** @use DateUnitTrait<TDayOfMonth> */
    use DateUnitTrait;

    /** @psalm-param TDayOfMonth $value */
    public static function of(int|self $value): self
    {
        return $value instanceof self ? $value : self::weak(value: $value);
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
    public function __toString(): string
    {
        return \sprintf('%02d', $this->number());
    }

    /** @internal */
    public function __debugInfo(): array
    {
        return ['dayOfMonth' => (string) $this];
    }

    /** @internal */
    #[\Override]
    public function withinDate(Date $date): Date
    {
        return Date::of(
            year: $date->year(),
            month: $date->month(),
            day: $date->month()->adjustDay($this, $date->year()),
        );
    }

    #[\Override]
    protected static function minValue(): int
    {
        return 1;
    }

    #[\Override]
    protected static function maxValue(): int
    {
        return 31;
    }
}
