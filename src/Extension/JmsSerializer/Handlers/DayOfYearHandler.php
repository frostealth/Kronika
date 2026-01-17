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
use Kronika\Date\DayOfYear;

final readonly class DayOfYearHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [DayOfYear::class, 'KronikaDayOfYear'];
    }

    public function serialize(SerializationVisitor $visitor, ?DayOfYear $dayOfWeek, array $type): ?int
    {
        if ($dayOfWeek === null) {
            return $visitor->visitNull($dayOfWeek, $type);
        }

        return $visitor->visitInteger($dayOfWeek->number(), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?int $value, array $type): ?DayOfYear
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return DayOfYear::of($value);
    }
}
