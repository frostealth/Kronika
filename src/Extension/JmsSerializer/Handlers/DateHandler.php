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
    final public const string FORMAT = 'Y-m-d';

    /** @param non-empty-string $format */
    public function __construct(
        private string $format,
    ) {
    }

    #[\Override]
    public function types(): array
    {
        return [Date::class, 'KronikaDate'];
    }

    public function serialize(SerializationVisitor $visitor, ?Date $date, array $type, Context $context): mixed
    {
        if ($date === null) {
            return $visitor->visitNull($date, $type);
        }

        return $visitor->visitString($date->format($this->getFormat($type)), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, mixed $value, array $type, Context $context): ?Date
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null || $value === '') {
            return $visitor->visitNull($value, $type);
        }

        return Date::fromFormat($this->getFormat($type), $value);
    }

    /** @return non-empty-string */
    private function getFormat(array $type): string
    {
        return $type['params'][0] ?? $this->format;
    }
}
