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
use Kronika\Instant;

final readonly class InstantHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Instant::class, 'KronikaInstant'];
    }

    public function serialize(SerializationVisitor $visitor, ?Instant $instant, array $type): ?string
    {
        if ($instant === null) {
            return $visitor->visitNull($instant, $type);
        }

        return $visitor->visitString((string)$instant, $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type): ?Instant
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null || $value === '') {
            return $visitor->visitNull($value, $type);
        }

        return Instant::ofValue($value);
    }
}
