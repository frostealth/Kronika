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

namespace Kronika\Extension\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Kronika\LocalDateTime;

final class LocalDateTimeType extends Type
{
    final public const string NAME = 'kronika.local-datetime';

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof LocalDateTime) {
            throw InvalidType::new($value, $this->getName(), ['null', LocalDateTime::class]);
        }

        return $value->format($platform->getDateTimeFormatString());
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?LocalDateTime
    {
        if ($value === null) {
            return null;
        }

        try {
            \assert(\is_string($value && $value !== ''));

            return LocalDateTime::tryFromFormat($platform->getDateTimeFormatString(), $value)
                ?? LocalDateTime::parse($value);
        } catch (\Throwable $e) {
            throw ValueNotConvertible::new($value, $this->getName(), previous: $e);
        }
    }

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
