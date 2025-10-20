<?php

declare(strict_types=1);

namespace Kronika\Date;

use Kronika\Date;
use Kronika\LocalDateTime;

/**
 * @psalm-require-implements DateUnit
 * @psalm-import-type DateUnitValue from DateUnit
 * @psalm-internal Kronika\Date
 * @implements DateUnit<DateUnitValue>
 * @internal
 */
trait DateUnitTrait
{
    abstract protected static function minValue(): int;
    abstract protected static function maxValue(): int;

    /** @psalm-param DateUnitValue $value */
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

    #[\Override]
    final public function withinDateTime(LocalDateTime $dateTime): LocalDateTime
    {
        return $dateTime->with($this->withinDate($dateTime->date()));
    }

    #[\Override]
    abstract public function withinDate(Date $date): Date;
}
