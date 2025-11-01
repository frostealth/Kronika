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
use Kronika\Time;

final readonly class TimeHandler implements Handler
{
    final public const string FORMAT = 'H:i:s.u';

    /** @param non-empty-string $format */
    public function __construct(
        private string $format,
    ) {
    }

    #[\Override]
    public function types(): array
    {
        return [Time::class, 'KronikaTime'];
    }

    public function serialize(SerializationVisitor $visitor, ?Time $time, array $type): ?string
    {
        if ($time === null) {
            return $visitor->visitNull($time, $type);
        }

        return $visitor->visitString($time->format($this->getFormat($type)), $type);
    }

    public function deserialize(DeserializationVisitor $visitor, ?string $value, array $type): ?Time
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null || $value === '') {
            return $visitor->visitNull($value, $type);
        }

        return Time::ofFormat($this->getFormat($type), $value);
    }

    /** @return non-empty-string */
    private function getFormat(array $type): string
    {
        return $type['params'][0] ?? $this->format;
    }
}
