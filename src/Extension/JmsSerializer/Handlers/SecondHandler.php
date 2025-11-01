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
use Kronika\Time\Second;

final readonly class SecondHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Second::class, 'KronikaSecond'];
    }

    public function serialize(SerializationVisitor $visitor, ?Second $second, array $type): ?string
    {
        if ($second === null) {
            return $visitor->visitNull($second, $type);
        }

        return $visitor->visitString(\sprintf('%02d.%06d', $second->second(), $second->microsecond()), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type): ?Second
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$second, $micro] = \sscanf($value, '%2d.%6d');

        return Second::of(second: (int)$second, micro: (int)$micro);
    }
}
