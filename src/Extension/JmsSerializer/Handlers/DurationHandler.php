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

namespace Kronika\Extension\JmsSerializer\Handlers;

use JMS\Serializer\Visitor\DeserializationVisitorInterface as DeserializationVisitor;
use JMS\Serializer\Visitor\SerializationVisitorInterface as SerializationVisitor;
use Kronika\Duration;

final readonly class DurationHandler implements Handler
{
    final public const string FORMAT_TIME_INTERVAL = 'time_interval';
    final public const string FORMAT_TOTAL_SECONDS = 'total_seconds';
    final public const string FORMAT_TOTAL_MINUTES = 'total_minutes';
    final public const string FORMAT_TOTAL_HOURS = 'total_hours';
    final public const string FORMAT_TOTAL_DAYS = 'total_days';
    final public const string FORMAT_ARRAY = 'array';

    /** @deprecated {@see self::FORMAT_TOTAL_SECONDS} */
    final public const string FORMAT_IN_SECONDS = 'in_seconds';
    /** @deprecated {@see self::FORMAT_TOTAL_MINUTES} */
    final public const string FORMAT_IN_MINUTES = 'in_minutes';
    /** @deprecated {@see self::FORMAT_TOTAL_HOURS} */
    final public const string FORMAT_IN_HOURS = 'in_hours';
    /** @deprecated {@see self::FORMAT_TOTAL_DAYS} */
    final public const string FORMAT_IN_DAYS = 'in_days';

    public function __construct(
        private string $format = self::FORMAT_TIME_INTERVAL,
    ) {
    }

    #[\Override]
    public function types(): array
    {
        return [Duration::class, 'KronikaDuration'];
    }

    public function serialize(SerializationVisitor $visitor, ?Duration $duration, array $type): null|array|int|string
    {
        if ($duration === null) {
            return $visitor->visitNull($duration, $type);
        }

        return match($this->getFormat($type)) {
            self::FORMAT_TIME_INTERVAL => $visitor->visitString(\sprintf(
                '%02d:%02d:%02d.%06d',
                $duration->totalHours(),
                $duration->minutes(),
                $duration->seconds(),
                $duration->microseconds(),
            ), $type),
            self::FORMAT_TOTAL_SECONDS, self::FORMAT_IN_SECONDS => $visitor->visitInteger($duration->totalSeconds(), $type),
            self::FORMAT_TOTAL_MINUTES, self::FORMAT_IN_MINUTES => $visitor->visitInteger($duration->totalMinutes(), $type),
            self::FORMAT_TOTAL_HOURS, self::FORMAT_IN_HOURS => $visitor->visitInteger($duration->totalHours(), $type),
            self::FORMAT_TOTAL_DAYS, self::FORMAT_IN_DAYS => $visitor->visitInteger($duration->totalDays(), $type),
            self::FORMAT_ARRAY => [
                'days' => $duration->days(),
                'hours' => $duration->hours(),
                'minutes' => $duration->minutes(),
                'seconds' => $duration->seconds(),
                'micros' => $duration->microseconds(),
            ],
        };
    }

    public function deserialize(DeserializationVisitor $visitor, null|array|int|string $value, array $type): ?Duration
    {
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        $format = $this->getFormat($type);
        $value = match($format) {
            self::FORMAT_TIME_INTERVAL => $visitor->visitString($value, $type),
            self::FORMAT_ARRAY => $value,
            default => $visitor->visitInteger($value, $type),
        };

        if ($value === null || $value === '' || $value === []) {
            return $visitor->visitNull($value, $type);
        }

        /** @psalm-suppress InvalidArgument, PossiblyInvalidArgument */
        return match($format) {
            self::FORMAT_TIME_INTERVAL => (static function (string $value): Duration {
                \sscanf($value, '%d:%d:%d.%6d', $hours, $minutes, $seconds, $micros);

                /** @psalm-suppress InvalidScalarArgument */
                return Duration::of(hours: $hours, minutes: $minutes, seconds: $seconds, micros: $micros);
            })($value),
            self::FORMAT_TOTAL_SECONDS, self::FORMAT_IN_SECONDS => Duration::of(seconds: $value),
            self::FORMAT_TOTAL_MINUTES, self::FORMAT_IN_MINUTES => Duration::of(minutes: $value),
            self::FORMAT_TOTAL_HOURS, self::FORMAT_IN_HOURS => Duration::of(hours: $value),
            self::FORMAT_TOTAL_DAYS, self::FORMAT_IN_DAYS => Duration::of(days: $value),
            self::FORMAT_ARRAY => Duration::of(...$value),
        };
    }

    private function getFormat(array $type): string
    {
        return $type['params'][0] ?? $this->format;
    }
}
