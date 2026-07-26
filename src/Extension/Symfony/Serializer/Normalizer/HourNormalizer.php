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

use Kronika\Time\Hour;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class HourNormalizer implements Normalizer, Denormalizer
{
    /** @psalm-suppress LessSpecificImplementedReturnType */
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Hour::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Hour;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Hour::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): int
    {
        if (! $data instanceof Hour) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Hour::class));
        }

        return $data->value();
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Hour
    {
        if (! \is_int($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['integer'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        try {
            /** @psalm-suppress InvalidArgument, PossiblyInvalidArgument */
            return Hour::of($data);
        } catch (\Throwable $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: $e->getMessage(),
                data: $data,
                expectedTypes: ['integer'],
                path: $context['deserialization_path'] ?? null,
                code: $e->getCode(),
            );
        }
    }
}
