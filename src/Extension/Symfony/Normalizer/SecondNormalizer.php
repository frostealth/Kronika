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

use Kronika\Time\Second;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;
use function Kronika\Utils\Math\double_split;

final readonly class SecondNormalizer implements Normalizer, Denormalizer
{
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Second::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Second;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Second::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): float
    {
        if (! $data instanceof Second) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Second::class));
        }

        return $data->value();
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Second
    {
        if (! \is_float($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['float'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        return Second::of(...double_split($data, precision: 6));
    }
}
