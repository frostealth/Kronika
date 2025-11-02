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
use Kronika\Date;
use Kronika\DateTime;
use Kronika\Time;

/**
 * @template TUnit of DateTime|Date|Time
 *
 * @implements CastsAttributes<TUnit, non-empty-string>
 * @internal
 * @psalm-internal Kronika\Extension\Laravel\Eloquent\Casts
 */
abstract readonly class CastsFormattableUnit implements CastsAttributes
{
    /** @param non-empty-string $format */
    public function __construct(
        private string $format,
    ) {
    }

    #[\Override]
    final public function get(Model $model, string $key, mixed $value, array $attributes): null|DateTime|Date|Time
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! \is_string($value)) {
            throw new \InvalidArgumentException('Invalid value.');
        }

        return static::factory()($this->format, $value);
    }

    #[\Override]
    final public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        if (
            ! $value instanceof DateTime
            && ! $value instanceof Date
            && ! $value instanceof Time
        ) {
            throw new \InvalidArgumentException('Invalid type.');
        }

        return $value->format($this->format);
    }

    /** @return callable(non-empty-string, non-empty-string): TUnit */
    abstract protected static function factory(): callable;
}
