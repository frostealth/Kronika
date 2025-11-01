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
    final public const string FORMAT_IN_SECONDS = 'in_seconds';
    final public const string FORMAT_IN_MINUTES = 'in_minutes';
    final public const string FORMAT_IN_HOURS = 'in_hours';
    final public const string FORMAT_IN_DAYS = 'in_days';
    final public const string FORMAT_ARRAY = 'array';

    /** @param self::FORMAT_* $format */
    public function __construct(
        private string $format = self::FORMAT_IN_SECONDS,
    ) {
    }

    #[\Override]
    public function types(): array
    {
        return [Duration::class, 'KronikaDuration'];
    }

    public function serialize(SerializationVisitor $visitor, ?Duration $duration, array $type): null|int|array
    {
        if ($duration === null) {
            return $visitor->visitNull($duration, $type);
        }

        return match($this->getFormat($type)) {
            self::FORMAT_IN_SECONDS => $visitor->visitInteger($duration->inSeconds(), $type),
            self::FORMAT_IN_MINUTES => $visitor->visitInteger($duration->inMinutes(), $type),
            self::FORMAT_IN_HOURS => $visitor->visitInteger($duration->inHours(), $type),
            self::FORMAT_IN_DAYS => $visitor->visitInteger($duration->inDays(), $type),
            self::FORMAT_ARRAY => [
                'days' => $duration->days(),
                'hours' => $duration->hours(),
                'minutes' => $duration->minutes(),
                'seconds' => $duration->seconds(),
            ],
        };
    }

    public function deserialize(DeserializationVisitor $visitor, null|int|array $value, array $type): ?Duration
    {
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        $format = $this->getFormat($type);
        $value = match($format) {
            self::FORMAT_ARRAY => $value,
            default => $visitor->visitInteger($value, $type),
        };

        if ($value === null || $value === []) {
            return $visitor->visitNull($value, $type);
        }

        return match($format) {
            self::FORMAT_IN_SECONDS => Duration::of(seconds: $value),
            self::FORMAT_IN_MINUTES => Duration::of(minutes: $value),
            self::FORMAT_IN_HOURS => Duration::of(hours: $value),
            self::FORMAT_IN_DAYS => Duration::of(days: $value),
            self::FORMAT_ARRAY => Duration::of(...$value),
        };
    }

    private function getFormat(array $type): string
    {
        return $type['params'][0] ?? $this->format;
    }
}
