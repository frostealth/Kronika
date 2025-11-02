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

use Kronika\DateTime;
use Kronika\LocalDateTime;
use Kronika\ZonedDateTime;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as Denormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as Normalizer;
use function Kronika\Utils\Math\double;

final readonly class DateTimeNormalizer implements Normalizer, Denormalizer
{
    final public const string KEY_FORMAT_LOCAL = 'kronika_local_datetime_format';
    final public const string KEY_FORMAT_ZONED = 'kronika_zoned_datetime_format';
    final public const string DEFAULT_FORMAT_LOCAL = 'Y-m-d\TH:i:s.u';
    final public const string DEFAULT_FORMAT_ZONED = 'Y-m-d\TH:i:s.uP';

    /**
     * @param non-empty-string $formatLocal
     * @param non-empty-string $formatZoned
     */
    public function __construct(
        private string $formatLocal,
        private string $formatZoned,
    ) {
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [LocalDateTime::class => true, ZonedDateTime::class => true];
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DateTime;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_a($type, DateTime::class, true);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): float|int|string
    {
        if (! $data instanceof DateTime) {
            throw new InvalidArgumentException(\sprintf(
                'The object must be an instance of "%s" or "%s".',
                LocalDateTime::class,
                ZonedDateTime::class
            ));
        }

        $format = $this->getFormat($data::class, $context);

        return match ($format) {
            'U' => (int)$data->format($format),
            'U.u' => double($data->format($format)),
            default => $data->format($format),
        };
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DateTime
    {
        if (! \is_float($data) && ! \is_string($data) && ! \is_int($data) || \trim($data) === '') {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                message: 'Unsupported type',
                data: $data,
                expectedTypes: ['float', 'integer', 'string'],
                path: $context['deserialization_path'] ?? null,
            );
        }
        if (\is_float($data)) {
            $data = \sprintf('%.06F', $data);
        }

        $format = $this->getFormat($type, $context);

        return match ($type) {
            LocalDateTime::class => LocalDateTime::ofFormat($format, (string)$data),
            ZonedDateTime::class => ZonedDateTime::ofFormat($format, (string)$data),
        };
    }

    private function getFormat(string $type, array $context): string
    {
        return match ($type) {
            LocalDateTime::class => $context[self::KEY_FORMAT_LOCAL] ?? $this->formatLocal,
            ZonedDateTime::class => $context[self::KEY_FORMAT_ZONED] ?? $this->formatZoned,
        };
    }
}
