<?php

declare(strict_types=1);

namespace Kronika\Date;

use Kronika\Date;
use Kronika\LocalDateTime;
use Kronika\Utils\WeakRefsTrait;

/**
 * @template TDateUnit of int
 * @psalm-require-implements DateUnit
 * @psalm-internal Kronika\Date
 * @implements DateUnit<TDateUnit>
 * @internal
 */
trait DateUnitTrait
{
    /** @use WeakRefsTrait<static,TDateUnit> */
    use WeakRefsTrait;

    abstract protected static function minValue(): int;
    abstract protected static function maxValue(): int;

    /** @psalm-param TDateUnit $value */
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

    /** @internal */
    #[\Override]
    final public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinDate($dateTime->date()));
    }

    /** @internal */
    #[\Override]
    abstract public function withinDate(Date $date): Date;
}
