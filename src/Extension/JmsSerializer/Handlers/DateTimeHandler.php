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
use Kronika\DateTime;
use Kronika\LocalDateTime;
use Kronika\ZonedDateTime;

final readonly class DateTimeHandler implements Handler
{
    final public const string FORMAT_LOCAL = 'Y-m-d\TH:i:s.u';
    final public const string FORMAT_ZONED = 'Y-m-d\TH:i:s.uP';

    private const array LOCAL = [LocalDateTime::class, 'KronikaLocalDateTime'];
    private const array ZONED = [ZonedDateTime::class, 'KronikaZonedDateTime'];

    /**
     * @param non-empty-string $formatLocal
     * @param non-empty-string $formatZoned
     */
    public function __construct(
        private string $formatLocal,
        private string $formatZoned,
    ) {
    }

    #[\Override]
    public function types(): array
    {
        return [...self::LOCAL, ...self::ZONED];
    }

    public function serialize(SerializationVisitor $visitor, ?DateTime $datetime, array $type): null|float|int|string
    {
        if ($datetime === null) {
            return $visitor->visitNull($datetime, $type);
        }

        $format = $this->getFormat($type);
        $value = $datetime->format($format);

        return match ($format) {
            'U' => $visitor->visitInteger((int)$value, $type),
            default => $visitor->visitString($value, $type),
        };
    }

    public function deserialize(DeserializationVisitor $visitor, null|float|int|string $value, array $type): ?DateTime
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null || $value === '') {
            return $visitor->visitNull($value, $type);
        }

        $format = $this->getFormat($type);

        return match ($this->normalizeType($type)) {
            LocalDateTime::class => LocalDateTime::fromFormat($format, $value),
            ZonedDateTime::class => ZonedDateTime::fromFormat($format, $value),
        };
    }

    /** @return non-empty-string */
    private function getFormat(array $type): string
    {
        return $type['params'][0] ?? match ($this->normalizeType($type)) {
            LocalDateTime::class => $this->formatLocal,
            ZonedDateTime::class => $this->formatZoned,
        };
    }

    /** @return class-string<LocalDateTime|ZonedDateTime> */
    private function normalizeType(array $type): string
    {
        $type = $type['name'];

        return match (true) {
            \in_array($type, self::LOCAL) => LocalDateTime::class,
            \in_array($type, self::ZONED) => ZonedDateTime::class,
        };
    }
}
