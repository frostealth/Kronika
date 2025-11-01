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
use Kronika\Time\Hour;

final readonly class HourHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Hour::class, 'KronikaHour'];
    }

    public function serialize(SerializationVisitor $visitor, ?Hour $hour, array $type): ?int
    {
        if ($hour === null) {
            return $visitor->visitNull($hour, $type);
        }

        return $visitor->visitInteger($hour->value(), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?int $value, array $type): ?Hour
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Hour::of($value);
    }
}
