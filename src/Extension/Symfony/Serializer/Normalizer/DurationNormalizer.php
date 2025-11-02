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

use Kronika\Duration;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;

final readonly class DurationNormalizer implements Normalizer, Denormalizer
{
    final public const string KEY_FORMAT = 'kronika_duration_format';
    final public const string DEFAULT_FORMAT = self::FORMAT_IN_SECONDS;
    final public const string FORMAT_IN_SECONDS = 'in_seconds';
    final public const string FORMAT_IN_MINUTES = 'in_minutes';
    final public const string FORMAT_IN_HOURS = 'in_hours';
    final public const string FORMAT_IN_DAYS = 'in_days';
    final public const string FORMAT_ARRAY = 'array';

    /** @param self::FORMAT_* $format */
    public function __construct(
        private string $format,
    ) {
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [Duration::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Duration;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, Duration::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): int|array
    {
        if (! $data instanceof Duration) {
            throw new InvalidArgumentException(\sprintf('The object must be an instance of "%s".', Duration::class));
        }

        return match($this->getFormat($context)) {
            self::FORMAT_IN_SECONDS => $data->inSeconds(),
            self::FORMAT_IN_MINUTES => $data->inMinutes(),
            self::FORMAT_IN_HOURS => $data->inHours(),
            self::FORMAT_IN_DAYS => $data->inDays(),
            self::FORMAT_ARRAY => [
                'days' => $data->days(),
                'hours' => $data->hours(),
                'minutes' => $data->minutes(),
                'seconds' => $data->seconds(),
            ],
        };
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Duration
    {
        if (! \is_int($data) && ! \is_array($data) || $data === []) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['array', 'integer'],
                path: $context['deserialization_path'] ?? null,
            );
        }
        $format = $this->getFormat($context);
        $this->assertValue($format, $data, $context);

        try {
            return match ($this->getFormat($context)) {
                self::FORMAT_IN_SECONDS => Duration::of(seconds: $data),
                self::FORMAT_IN_MINUTES => Duration::of(minutes: $data),
                self::FORMAT_IN_HOURS => Duration::of(hours: $data),
                self::FORMAT_IN_DAYS => Duration::of(days: $data),
                self::FORMAT_ARRAY => Duration::of(...$data),
            };
        } catch (\Throwable $e) {
            throw new NotNormalizableValueException('Not a valid type', previous: $e);
        }
    }

    private function getFormat(array $context): string
    {
        return $context[self::KEY_FORMAT] ?? $this->format;
    }

    private function assertValue(string $format, mixed $value, array $context): void
    {
        if ($format !== self::FORMAT_ARRAY && ! \is_int($value)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $value,
                expectedTypes: ['integer'],
                path: $context['deserialization_path'] ?? null,
            );
        }
        if ($format === self::FORMAT_ARRAY && ! \is_array($value)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $value,
                expectedTypes: ['array'],
                path: $context['deserialization_path'] ?? null,
            );
        }
    }
}
