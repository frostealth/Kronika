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
    #[\Override]
    public function types(): array
    {
        return [Duration::class, 'KronikaDuration'];
    }

    public function serialize(SerializationVisitor $visitor, ?Duration $duration, array $type): ?int
    {
        if ($duration === null) {
            return $visitor->visitNull($duration, $type);
        }

        return $visitor->visitInteger($duration->inSeconds(), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?int $value, array $type): ?Duration
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Duration::of(seconds: $value);
    }
}
