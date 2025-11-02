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

namespace Kronika\Extension\Symfony\Serializer\Normalizer;

use Kronika\Date\Year;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class YearNormalizer implements Normalizer, Denormalizer
{
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Year::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Year;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Year::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): int
    {
        if (! $data instanceof Year) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Year::class));
        }

        return $data->number();
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Year
    {
        if (! \is_int($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['integer'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        return Year::of($data);
    }
}
