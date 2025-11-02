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
use Kronika\Duration;

/**
 * @psalm-type TValue=non-negative-int
 * @psalm-type TArrayDuration=array{days: TValue, hours: TValue, minutes: TValue, seconds:TValue}
 *
 * @implements CastsAttributes<Duration, TArrayDuration>
 */
final readonly class AsArrayDuration implements CastsAttributes
{
    #[\Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Duration
    {
        if ($value === null || $value === []) {
            return null;
        }
        if (! is_array($value)) {
            throw new \InvalidArgumentException('Invalid value.');
        }

        return Duration::of(...$value);
    }

    #[\Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof Duration) {
            throw new \InvalidArgumentException('Invalid type.');
        }

        return [
            'days' => $value->days(),
            'hours' => $value->hours(),
            'minutes' => $value->minutes(),
            'seconds' => $value->seconds(),
        ];
    }
}
