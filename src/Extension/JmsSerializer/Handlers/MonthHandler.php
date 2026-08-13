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
use Kronika\Date\Month;

final readonly class MonthHandler implements Handler
{
    #[\Override]
    public function types(): array
    {
        return [Month::class, 'KronikaMonth'];
    }

    public function serialize(SerializationVisitor $visitor, ?Month $month, array $type): mixed
    {
        if ($month === null) {
            return $visitor->visitNull($month, $type);
        }

        return $visitor->visitInteger($month->number(), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, mixed $value, array $type): ?Month
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Month::of($value);
    }
}
