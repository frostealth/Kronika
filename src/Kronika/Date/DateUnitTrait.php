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
use Kronika\DateTime;
use Kronika\LocalDateTime;
use Kronika\Utils\WeakRefsTrait;

/**
 * @template TDateUnit of int
 * @psalm-require-implements DateUnit
 * @implements DateUnit<TDateUnit>
 *
 * @psalm-internal Kronika\Date
 * @internal
 */
trait DateUnitTrait
{
    /** @use WeakRefsTrait<static,TDateUnit> */
    use WeakRefsTrait;

    abstract protected static function minValue(): int;
    abstract protected static function maxValue(): int;

    /** @param TDateUnit $value */
    final protected function __construct(
        private readonly int $value,
    ) {
        \assert(static::minValue() <= $value && $value <= static::maxValue());
    }

    #[\Override]
    final public function is(int $number): bool
    {
        return $this->number() === $number;
    }

    #[\Override]
    final public function number(): int
    {
        return $this->value;
    }

    /** @internal {@see Date::with()} */
    #[\Override]
    abstract public function _withinDate(Date $date): Date;

    /** @internal {@see DateTime::with()} */
    #[\Override]
    final public function _withinDateTime(LocalDateTime $datetime): LocalDateTime
    {
        return $datetime->with($this->_withinDate($datetime->date()));
    }
}
