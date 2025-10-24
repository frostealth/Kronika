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

namespace Kronika\Extension\Doctrine\Types\Time;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Kronika\Time\Second;

final class SecondType extends Type
{
    final public const string NAME = 'kronika.second';

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?float
    {
        if ($value === null) {
            return null;
        }
        if (! $value instanceof Second) {
            throw InvalidType::new($value, $this->getName(), ['null', Second::class]);
        }

        return $value->value();
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Second
    {
        if ($value === null) {
            return null;
        }

        try {
            \assert(\is_string($value));
            [$second, $micro] = \sscanf($value, '%d.%6d');

            return Second::of(second: (int) $second, micro: (int) $micro);
        } catch (\Throwable $e) {
            throw ValueNotConvertible::new($value, $this->getName(), previous: $e);
        }
    }

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getSmallFloatDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
