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
use Kronika\Duration;

final class DurationType extends Type
{
    final public const string NAME = 'kronika.duration';

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof Duration) {
            throw InvalidType::new($value, $this->getName(), ['null', Duration::class]);
        }

        return $value->totalSeconds();
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Duration
    {
        if ($value === null) {
            return null;
        }

        try {
            return Duration::of(seconds: (int)$value);
        } catch (\Throwable $e) {
            throw ValueNotConvertible::new($value, $this->getName(), previous: $e);
        }
    }

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
