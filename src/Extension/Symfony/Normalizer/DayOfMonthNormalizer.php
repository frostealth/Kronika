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

namespace Kronika\Extension\Symfony\Normalizer;

use Kronika\Date\DayOfMonth;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class DayOfMonthNormalizer implements Normalizer, Denormalizer
{
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [DayOfMonth::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DayOfMonth;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, DayOfMonth::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): int
    {
        if (! $data instanceof DayOfMonth) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', DayOfMonth::class));
        }

        return $data->number();
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DayOfMonth
    {
        if (! \is_int($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['integer'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        return DayOfMonth::of($data);
    }
}
