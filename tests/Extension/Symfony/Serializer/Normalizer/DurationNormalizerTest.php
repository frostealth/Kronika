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

use Kronika\Duration;
use Kronika\Extension\Symfony\Serializer\Normalizer\DurationNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(DurationNormalizer::class)]
final class DurationNormalizerTest extends TestCase
{
    private static Serializer $serializer;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$serializer = new Serializer(
            normalizers: [new DurationNormalizer(format: DurationNormalizer::DEFAULT_FORMAT)],
        );
    }

    private static function duration(): Duration
    {
        static $duration = Duration::of(days: 28, hours: 23, minutes: 35, seconds: 15);

        return $duration;
    }

    public static function normalizeIntegerProvider(): array
    {
        return [
            [DurationNormalizer::FORMAT_IN_SECONDS, self::duration()->inSeconds()],
            [DurationNormalizer::FORMAT_IN_MINUTES, self::duration()->inMinutes()],
            [DurationNormalizer::FORMAT_IN_HOURS, self::duration()->inHours()],
            [DurationNormalizer::FORMAT_IN_DAYS, self::duration()->inDays()],
        ];
    }

    #[DataProvider('normalizeIntegerProvider')]
    public function testNormalizeInteger(string $format, int $expected): void
    {
        $actual = self::$serializer->normalize(self::duration(), context: [
            DurationNormalizer::KEY_FORMAT => $format,
        ]);

        self::assertIsInt($actual);
        self::assertEquals($expected, $actual);
    }

    public static function denormalizeIntegerProvider(): array
    {
        return [
            [DurationNormalizer::FORMAT_IN_SECONDS, self::duration()->inSeconds(), self::duration()],
            [DurationNormalizer::FORMAT_IN_MINUTES, self::duration()->inMinutes(), self::duration()->roundToMinutes()],
            [DurationNormalizer::FORMAT_IN_HOURS,   self::duration()->inHours(),   self::duration()->roundToHours()],
            [DurationNormalizer::FORMAT_IN_DAYS,    self::duration()->inDays(),    self::duration()->roundToDays()],
        ];
    }

    #[DataProvider('denormalizeIntegerProvider')]
    public function testDenormalizeInteger(string $format, int $data, Duration $expected): void
    {
        $actual = self::$serializer->denormalize($data, Duration::class, context: [
            DurationNormalizer::KEY_FORMAT => $format,
        ]);

        self::assertInstanceOf(Duration::class, $actual);
        self::assertEquals($expected, $actual);
    }

    public function testNormalizeArray(): void
    {
        $actual = self::$serializer->normalize(self::duration(), context: [
            DurationNormalizer::KEY_FORMAT => DurationNormalizer::FORMAT_ARRAY,
        ]);

        self::assertIsArray($actual);
        self::assertEquals([
            'days' => self::duration()->days(),
            'hours' => self::duration()->hours(),
            'minutes' => self::duration()->minutes(),
            'seconds' => self::duration()->seconds(),
        ], $actual);
    }

    public function testDenormalizeArray(): void
    {
        $actual = self::$serializer->denormalize([
            'days' => self::duration()->days(),
            'hours' => self::duration()->hours(),
            'minutes' => self::duration()->minutes(),
            'seconds' => self::duration()->seconds(),
        ], Duration::class, context: [DurationNormalizer::KEY_FORMAT => DurationNormalizer::FORMAT_ARRAY]);

        self::assertInstanceOf(Duration::class, $actual);
        self::assertEquals(self::duration(), $actual);
    }
}
