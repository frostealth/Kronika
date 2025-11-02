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

use Kronika\Instant;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class InstantNormalizer implements Normalizer, Denormalizer
{
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Instant::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Instant;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Instant::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): float|int|string
    {
        if (! $data instanceof Instant) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Instant::class));
        }

        return $data->value();
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Instant
    {
        if (! \is_float($data) && ! \is_string($data) && ! \is_int($data) || $data === '') {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['float', 'integer', 'string'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        return Instant::ofValue($data);
    }
}
