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
 * @implements CastsAttributes<Duration, non-negative-int>
 */
final readonly class AsDuration implements CastsAttributes
{
    final public const string FORMAT_IN_SECONDS = 'in_seconds';
    final public const string FORMAT_IN_MINUTES = 'in_minutes';
    final public const string FORMAT_IN_HOURS = 'in_hours';
    final public const string FORMAT_IN_DAYS = 'in_days';

    /** @param self::FORMAT_* $format */
    public function __construct(
        private string $format = self::FORMAT_IN_SECONDS,
    ) {
    }

    #[\Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Duration
    {
        if ($value === null) {
            return null;
        }
        if (! \is_int($value) || $value < 0) {
            throw new \InvalidArgumentException('Invalid value');
        }

        return match($this->format) {
            self::FORMAT_IN_SECONDS => Duration::of(seconds: $value),
            self::FORMAT_IN_MINUTES => Duration::of(minutes: $value),
            self::FORMAT_IN_HOURS => Duration::of(hours: $value),
            self::FORMAT_IN_DAYS => Duration::of(days: $value),
        };
    }

    #[\Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof Duration) {
            throw new \InvalidArgumentException('Invalid type');
        }

        return match($this->format) {
            self::FORMAT_IN_SECONDS => $value->inSeconds(),
            self::FORMAT_IN_MINUTES => $value->inMinutes(),
            self::FORMAT_IN_HOURS => $value->inHours(),
            self::FORMAT_IN_DAYS => $value->inDays(),
        };
    }
}
