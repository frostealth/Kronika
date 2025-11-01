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
use Kronika\Date;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Time\Second;

final readonly class LocalDateTimeHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [LocalDateTime::class, 'KronikaLocalDateTime'];
    }

    public function serialize(SerializationVisitor $visitor, ?LocalDateTime $datetime, array $type): ?string
    {
        if ($datetime === null) {
            return $visitor->visitNull($datetime, $type);
        }

        return $visitor->visitString(\sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d.%06d',
            $datetime->year()->number(),
            $datetime->month()->number(),
            $datetime->day()->number(),
            $datetime->hour()->value(),
            $datetime->minute()->value(),
            $datetime->second()->second(),
            $datetime->second()->microsecond(),
        ), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type): ?LocalDateTime
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$year, $month, $day, $hour, $minute, $second, $micro] = \sscanf($value, '%4d-%2d-%2dT%2d:%2d:%2d.%6d');

        return LocalDateTime::of(
            date: Date::of(year: (int)$year, month: (int)$month, day: (int)$day),
            time: Time::of(hour: (int)$hour, minute: (int)$minute, second: Second::of(second: (int)$second, micro: (int)$micro)),
        );
    }
}
