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

namespace Kronika\Extension\Symfony\Serializer;

use Kronika\Date;
use Kronika\Duration;
use Kronika\Extension\Symfony\Serializer\Normalizer\DateNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\DateTimeNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\DayOfMonthNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\DayOfWeekNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\DayOfYearNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\DurationNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\HourNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\InstantNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\MinuteNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\MonthNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\SecondNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\TimeNormalizer;
use Kronika\Extension\Symfony\Serializer\Normalizer\YearNormalizer;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @psalm-type TFormattable=LocalDateTime|ZonedDateTime|Date|Time|Duration
 */
final readonly class KronikaNormalizers
{
    /**
     * @param array<class-string<TFormattable>, non-empty-string> $formats Default formats
     *
     * @return list<NormalizerInterface|DenormalizerInterface>
     */
    public static function build(array $formats = []): array
    {
        return [
            new DateNormalizer(format: $formats[Date::class] ?? DateNormalizer::DEFAULT_FORMAT),
            new YearNormalizer(), new MonthNormalizer(), new DayOfMonthNormalizer(), new DayOfWeekNormalizer(),
            new DayOfYearNormalizer(),

            new TimeNormalizer(format: $formats[Time::class] ?? TimeNormalizer::DEFAULT_FORMAT),
            new HourNormalizer(), new MinuteNormalizer(), new SecondNormalizer(),

            new DurationNormalizer(format: $formats[Duration::class] ?? DurationNormalizer::DEFAULT_FORMAT),
            new InstantNormalizer(),
            new DateTimeNormalizer(
                formatLocal: $formats[LocalDateTime::class] ?? DateTimeNormalizer::DEFAULT_FORMAT_LOCAL,
                formatZoned: $formats[ZonedDateTime::class] ?? DateTimeNormalizer::DEFAULT_FORMAT_ZONED,
            ),
        ];
    }

    private function __construct() {
    }
}
