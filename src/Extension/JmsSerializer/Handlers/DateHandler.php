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

use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface as DeserializationVisitor;
use JMS\Serializer\Visitor\SerializationVisitorInterface as SerializationVisitor;
use Kronika\Date;

final readonly class DateHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Date::class, 'KronikaDate'];
    }

    public function serialize(SerializationVisitor $visitor, ?Date $date, array $type, Context $context): ?string
    {
        if ($date === null) {
            return $visitor->visitNull($date, $type);
        }

        return $visitor->visitString(\sprintf(
            '%04d-%02d-%02d',
            $date->year()->number(),
            $date->month()->number(),
            $date->day()->number(),
        ), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type, Context $context): ?Date
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$year, $month, $day] = \sscanf($value, '%4d-%2d-%2d');

        return Date::of(year: (int)$year, month: (int)$month, day: (int)$day);
    }
}
