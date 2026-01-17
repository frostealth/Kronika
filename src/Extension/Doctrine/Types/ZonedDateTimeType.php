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
use Kronika\ZonedDateTime;

final class ZonedDateTimeType extends Type
{
    final public const string NAME = 'kronika.zoned-datetime';

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof ZonedDateTime) {
            throw InvalidType::new($value, $this->getName(), ['null', ZonedDateTime::class]);
        }

        return $value->format($platform->getDateTimeTzFormatString());
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ZonedDateTime
    {
        if ($value === null) {
            return null;
        }

        try {
            \assert(\is_string($value) && $value !== '');

            return ZonedDateTime::tryOfFormat($platform->getDateTimeTzFormatString(), $value)
                ?? ZonedDateTime::parse($value);
        } catch (\Throwable $e) {
            throw ValueNotConvertible::new($value, $this->getName(), previous: $e);
        }
    }

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTzTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
