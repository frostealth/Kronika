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
use Kronika\ZonedDateTime;

final readonly class ZonedDateTimeHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [ZonedDateTime::class, 'KronikaZonedDateTime'];
    }

    public function serialize(SerializationVisitor $visitor, ?ZonedDateTime $datetime, array $type): ?string
    {
        if ($datetime === null) {
            return $visitor->visitNull($datetime, $type);
        }

        return $visitor->visitString($datetime->format(\DateTimeInterface::ATOM), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type): ?ZonedDateTime
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return ZonedDateTime::ofFormat(\DateTimeInterface::ATOM, $value);
    }
}
