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
 * @implements CastsAttributes<Duration, non-negative-int|non-empty-string>
 */
final readonly class AsDuration implements CastsAttributes
{
    final public const string FORMAT_TIME_INTERVAL = 'time_interval';
    final public const string FORMAT_TOTAL_SECONDS = 'total_seconds';
    final public const string FORMAT_TOTAL_MINUTES = 'total_minutes';
    final public const string FORMAT_TOTAL_HOURS = 'total_hours';
    final public const string FORMAT_TOTAL_DAYS = 'total_days';

    /** @deprecated {@see self::FORMAT_TOTAL_SECONDS} */
    final public const string FORMAT_IN_SECONDS = 'in_seconds';
    /** @deprecated {@see self::FORMAT_TOTAL_MINUTES} */
    final public const string FORMAT_IN_MINUTES = 'in_minutes';
    /** @deprecated {@see self::FORMAT_TOTAL_HOURS} */
    final public const string FORMAT_IN_HOURS = 'in_hours';
    /** @deprecated {@see self::FORMAT_TOTAL_DAYS} */
    final public const string FORMAT_IN_DAYS = 'in_days';

    /** @param self::FORMAT_* $format */
    public function __construct(
        private string $format = self::FORMAT_TIME_INTERVAL,
    ) {
    }

    #[\Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Duration
    {
        if ($value === null) {
            return null;
        }
        if ((! \is_int($value) || $value < 0) && (! \is_string($value) || $value === '')) {
            throw new \InvalidArgumentException('Invalid value');
        }

        return match($this->format) {
            self::FORMAT_TIME_INTERVAL => (static function (string $value): Duration {
                \sscanf($value, '%d:%d:%d.%6d', $hours, $minutes, $seconds, $micros);

                /** @psalm-suppress InvalidScalarArgument */
                return Duration::of(hours: $hours, minutes: $minutes, seconds: $seconds, micros: $micros);
            })($value),
            self::FORMAT_TOTAL_SECONDS, self::FORMAT_IN_SECONDS => Duration::of(seconds: $value),
            self::FORMAT_TOTAL_MINUTES, self::FORMAT_IN_MINUTES => Duration::of(minutes: $value),
            self::FORMAT_TOTAL_HOURS, self::FORMAT_IN_HOURS => Duration::of(hours: $value),
            self::FORMAT_TOTAL_DAYS, self::FORMAT_IN_DAYS => Duration::of(days: $value),
        };
    }

    #[\Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): null|int|string
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof Duration) {
            throw new \InvalidArgumentException('Invalid type');
        }

        return match($this->format) {
            self::FORMAT_TIME_INTERVAL => \sprintf(
                '%02d:%02d:%02d.%06d',
                $value->totalHours(),
                $value->minutes(),
                $value->seconds(),
                $value->microseconds(),
            ),
            self::FORMAT_TOTAL_SECONDS, self::FORMAT_IN_SECONDS => $value->totalSeconds(),
            self::FORMAT_TOTAL_MINUTES, self::FORMAT_IN_MINUTES => $value->totalMinutes(),
            self::FORMAT_TOTAL_HOURS, self::FORMAT_IN_HOURS => $value->totalHours(),
            self::FORMAT_TOTAL_DAYS, self::FORMAT_IN_DAYS => $value->totalDays(),
        };
    }
}
