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

use Kronika\Time;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class TimeNormalizer implements Normalizer, Denormalizer
{
    final public const string KEY_FORMAT = 'kronika_time_format';
    final public const string DEFAULT_FORMAT = 'H:i:s.u';

    /** @param non-empty-string $format */
    public function __construct(
        private string $format,
    ) {
    }

    /** @psalm-suppress LessSpecificImplementedReturnType */
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Time::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Time;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Time::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        if (! $data instanceof Time) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Time::class));
        }

        return $data->format($this->getFormat($context));
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Time
    {
        if (! \is_string($data) || \trim($data) === '') {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['string'],
                path: $context['deserialization_path'] ?? null,
            );
        }

        try {
            return Time::fromFormat($this->getFormat($context), $data);
        } catch (\Throwable $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: $e->getMessage(),
                data: $data,
                expectedTypes: ['string'],
                path: $context['deserialization_path'] ?? null,
                code: $e->getCode(),
            );
        }
    }

    /** @return non-empty-string */
    private function getFormat(array $context): string
    {
        return $context[self::KEY_FORMAT] ?? $this->format;
    }
}
