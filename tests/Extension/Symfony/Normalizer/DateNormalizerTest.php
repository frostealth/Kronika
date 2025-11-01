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
use Kronika\Extension\Symfony\Normalizer\DateNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(DateNormalizer::class)]
final class DateNormalizerTest extends TestCase
{
    private static Serializer $serializer;
    private static Date $date;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$date = Date::of(2025, 12, 31);
        self::$serializer = new Serializer(
            normalizers: [new DateNormalizer(format: DateNormalizer::DEFAULT_FORMAT)],
        );
    }

    public function testNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$date);

        self::assertIsString($actual);
        self::assertEquals(self::$date->format(DateNormalizer::DEFAULT_FORMAT), $actual);
    }

    public function testNormalizeWithCustomFormat(): void
    {
        $format = 'F jS, Y';
        $actual = self::$serializer->normalize(self::$date, context: [
            DateNormalizer::KEY_FORMAT => $format,
        ]);

        self::assertIsString($actual);
        self::assertEquals(self::$date->format($format), $actual);
    }

    public function testDenormalize(): void
    {
        $format = DateNormalizer::DEFAULT_FORMAT;
        $actual = self::$serializer->denormalize(self::$date->format($format), Date::class);

        self::assertInstanceOf(Date::class, $actual);
        self::assertEquals(self::$date, $actual);
    }

    public function testDenormalizeWithCustomFormat(): void
    {
        $format = 'F jS, Y';
        $actual = self::$serializer->denormalize(self::$date->format($format), Date::class, context: [
            DateNormalizer::KEY_FORMAT => $format,
        ]);

        self::assertInstanceOf(Date::class, $actual);
        self::assertEquals(self::$date, $actual);
    }
}
