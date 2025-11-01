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
use Kronika\Time;

final readonly class TimeHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Time::class, 'KronikaTime'];
    }

    public function serialize(SerializationVisitor $visitor, ?Time $time, array $type): ?string
    {
        if ($time === null) {
            return $visitor->visitNull($time, $type);
        }

        return $visitor->visitString(\sprintf(
            '%02d:%02d:%02d.%06d',
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
        ), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type): ?Time
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$hour, $minute, $second, $micro] = \sscanf($value, '%2d:%2d:%2d.%6d');

        return Time::of(
            hour: (int) $hour,
            minute: (int) $minute,
            second: Time\Second::of(second: (int) $second, micro: (int) $micro),
        );
    }
}
