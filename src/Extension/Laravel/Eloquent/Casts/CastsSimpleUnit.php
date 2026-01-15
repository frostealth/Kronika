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

namespace Kronika\Extension\Laravel\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Kronika\Date\DateUnit;
use Kronika\Instant;
use Kronika\Time\TimeUnit;

/**
 * @template TUnit of DateUnit|TimeUnit|Instant
 * @template TValue of numeric
 *
 * @implements CastsAttributes<TUnit, TValue>
 * @internal
 * @psalm-internal Kronika\Extension\Laravel\Eloquent\Casts
 */
abstract readonly class CastsSimpleUnit implements CastsAttributes
{
    #[\Override]
    final public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes
    ): null|DateUnit|TimeUnit|Instant {
        if ($value === null) {
            return null;
        }
        if (! \is_int($value) && ! \is_float($value)) {
            throw new \InvalidArgumentException('Invalid value.');
        }

        return static::factory()($value);
    }

    #[\Override]
    final public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): null|float|int {
        if ($value === null) {
            return null;
        }
        if (
            ! $value instanceof DateUnit
            && ! $value instanceof TimeUnit
            && ! $value instanceof Instant
        ) {
            throw new \InvalidArgumentException('Invalid type.');
        }

        /** @psalm-suppress InaccessibleMethod Cannot access private method Kronika\Instant::number() */
        return match(true) {
            $value instanceof DateUnit => $value->number(),
            default => $value->value(),
        };
    }

    /** @return callable(TValue): TUnit */
    abstract protected static function factory(): callable;
}
