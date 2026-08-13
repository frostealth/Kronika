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

use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface as SerializationVisitor;
use Kronika\Date\Year;

final readonly class YearHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Year::class, 'KronikaYear'];
    }

    public function serialize(SerializationVisitor $visitor, ?Year $year, array $type): mixed
    {
        if ($year === null) {
            return $visitor->visitNull($year, $type);
        }

        return $visitor->visitInteger($year->number(), $type);
    }

    public function deserialize(DeserializationVisitorInterface $visitor, mixed $value, array $type): ?Year
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Year::of($value);
    }
}
