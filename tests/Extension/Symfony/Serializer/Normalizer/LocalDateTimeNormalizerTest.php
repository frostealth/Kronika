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

namespace Kronika\Extension\Tests\Symfony\Serializer\Normalizer;

use Kronika\Date;
use Kronika\Extension\Symfony\Serializer\Normalizer\DateTimeNormalizer;
use Kronika\LocalDateTime;
use Kronika\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(DateTimeNormalizer::class)]
final class LocalDateTimeNormalizerTest extends TestCase
{
    /** @var non-empty-string */
    private static string $defaultFormat;
    private static Serializer $serializer;
    private static LocalDateTime $datetime;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$defaultFormat = DateTimeNormalizer::DEFAULT_FORMAT_LOCAL;
        self::$datetime = LocalDateTime::of(
            Date::of(2025, 12, 31),
            Time::endOfDay(),
        );
        self::$serializer = new Serializer(
            normalizers: [new DateTimeNormalizer(
                formatLocal: self::$defaultFormat,
                formatZoned: DateTimeNormalizer::DEFAULT_FORMAT_ZONED,
            )],
        );
    }

    public function testNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$datetime);

        self::assertIsString($actual);
        self::assertEquals(self::$datetime->format(self::$defaultFormat), $actual);
    }

    #[TestWith(['l, d-M-Y H:i:s'])]
    #[TestWith(['D, d M Y H:i:s'])]
    #[TestWith(['l, d-M-Y H:i:s.u'])]
    public function testNormalizeWithCustomFormat(string $format): void
    {
        $actual = self::$serializer->normalize(self::$datetime, context: [
            DateTimeNormalizer::KEY_FORMAT_LOCAL => $format,
        ]);

        self::assertIsString($actual);
        self::assertEquals(self::$datetime->format($format), $actual);
    }

    public function testDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$datetime->format(self::$defaultFormat), LocalDateTime::class);

        self::assertInstanceOf(LocalDateTime::class, $actual);
        self::assertEquals(self::$datetime, $actual);
    }

    #[TestWith(['l, d-M-Y H:i:s'])]
    #[TestWith(['D, d M Y H:i:s'])]
    #[TestWith(['l, d-M-Y H:i:s.u'])]
    public function testDenormalizeWithCustomFormat(string $format): void
    {
        $actual = self::$serializer->denormalize(self::$datetime->format($format), LocalDateTime::class, context: [
            DateTimeNormalizer::KEY_FORMAT_LOCAL => $format,
        ]);

        self::assertInstanceOf(LocalDateTime::class, $actual);
        self::assertEquals(self::$datetime->format($format), $actual->format($format));
    }
}
