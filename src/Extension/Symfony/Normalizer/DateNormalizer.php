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

use Kronika\Date;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class DateNormalizer implements Normalizer, Denormalizer
{
    final public const string KEY_FORMAT = 'kronika_date_format';
    final public const string DEFAULT_FORMAT = 'Y-m-d';

    /** @param non-empty-string $format */
    public function __construct(
        private string $format,
    ) {
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Date::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Date;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Date::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        if (! $data instanceof Date) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Date::class));
        }

        return $data->format($this->getFormat($context));
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Date
    {
        if (! \is_string($data) || \trim($data) === '') {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['string'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        return Date::ofFormat($this->getFormat($context), $data);
    }

    private function getFormat(array $context): string
    {
        return $context[self::KEY_FORMAT] ?? $this->format;
    }
}
