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

namespace Kronika\Extension\Tests\Symfony\Normalizer;

use Kronika\Date;
use Kronika\Extension\Symfony\Normalizer\DateTimeNormalizer;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(DateTimeNormalizer::class)]
final class ZonedDateTimeNormalizerTest extends TestCase
{
    /** @var non-empty-string */
    private static string $defaultFormat;
    private static Serializer $serializer;
    private static ZonedDateTime $datetime;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$defaultFormat = DateTimeNormalizer::DEFAULT_FORMAT_ZONED;
        self::$datetime = ZonedDateTime::of(
            Date::of(2025, 12, 31),
            Time::endOfDay(),
            new \DateTimeZone('+02:30'),
        );
        self::$serializer = new Serializer(
            normalizers: [new DateTimeNormalizer(
                formatLocal: DateTimeNormalizer::DEFAULT_FORMAT_LOCAL,
                formatZoned: self::$defaultFormat,
            )],
        );
    }

    public function testNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$datetime);

        self::assertIsString($actual);
        self::assertEquals(self::$datetime->format(self::$defaultFormat), $actual);
    }

    #[TestWith([\DateTimeInterface::RSS])]
    #[TestWith([\DateTimeInterface::ATOM])]
    #[TestWith([\DateTimeInterface::COOKIE])]
    #[TestWith(['Y-m-d\TH:i:s.uP'])]
    #[TestWith(['U'])]
    #[TestWith(['U.u'])]
    public function testNormalizeWithCustomFormat(string $format): void
    {
        $actual = self::$serializer->normalize(self::$datetime, context: [
            DateTimeNormalizer::KEY_FORMAT_ZONED => $format,
        ]);

        if ($format === 'U') {
            self::assertIsInt($actual);
        } elseif ($format === 'U.u') {
            self::assertIsFloat($actual);
        } else {
            self::assertIsString($actual);
        }

        self::assertEquals(self::$datetime->format($format), $actual);
    }

    public function testDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$datetime->format(self::$defaultFormat), ZonedDateTime::class);

        self::assertInstanceOf(ZonedDateTime::class, $actual);
        self::assertEquals(self::$datetime, $actual);
    }

    #[TestWith([\DateTimeInterface::RSS])]
    #[TestWith([\DateTimeInterface::ATOM])]
    #[TestWith([\DateTimeInterface::COOKIE])]
    #[TestWith(['Y-m-d\TH:i:s.uP'])]
    #[TestWith(['U'])]
    #[TestWith(['U.u'])]
    public function testDenormalizeWithCustomFormat(string $format): void
    {
        $actual = self::$serializer->denormalize(self::$datetime->format($format), ZonedDateTime::class, context: [
            DateTimeNormalizer::KEY_FORMAT_ZONED => $format,
        ]);

        self::assertInstanceOf(ZonedDateTime::class, $actual);
        self::assertEquals(self::$datetime->format($format), $actual->format($format));
    }
}
