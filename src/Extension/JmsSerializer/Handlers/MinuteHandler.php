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
use Kronika\Time\Minute;

final readonly class MinuteHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Minute::class, 'KronikaMinute'];
    }

    public function serialize(SerializationVisitor $visitor, ?Minute $minute, array $type): mixed
    {
        if ($minute === null) {
            return $visitor->visitNull($minute, $type);
        }

        return $visitor->visitInteger($minute->value(), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, mixed $value, array $type): ?Minute
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Minute::of($value);
    }
}
