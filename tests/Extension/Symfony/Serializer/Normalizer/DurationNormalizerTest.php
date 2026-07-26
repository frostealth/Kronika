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
        static $duration = Duration::of(days: 28, hours: 23, minutes: 35, seconds: 15, micros: 999_999);

        return $duration;
    }

    public static function normalizeIntegerProvider(): array
    {
        return [
            [DurationNormalizer::FORMAT_TOTAL_SECONDS, self::duration()->totalSeconds()],
            [DurationNormalizer::FORMAT_TOTAL_MINUTES, self::duration()->totalMinutes()],
            [DurationNormalizer::FORMAT_TOTAL_HOURS, self::duration()->totalHours()],
            [DurationNormalizer::FORMAT_TOTAL_DAYS, self::duration()->totalDays()],
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
            [DurationNormalizer::FORMAT_TOTAL_SECONDS, self::duration()->totalSeconds(), self::duration()->roundToSeconds()],
            [DurationNormalizer::FORMAT_TOTAL_MINUTES, self::duration()->totalMinutes(), self::duration()->roundToMinutes()],
            [DurationNormalizer::FORMAT_TOTAL_HOURS,   self::duration()->totalHours(),   self::duration()->roundToHours()],
            [DurationNormalizer::FORMAT_TOTAL_DAYS,    self::duration()->totalDays(),    self::duration()->roundToDays()],
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
            'micros' => self::duration()->microseconds(),
        ], $actual);
    }

    public function testDenormalizeArray(): void
    {
        $actual = self::$serializer->denormalize([
            'days' => self::duration()->days(),
            'hours' => self::duration()->hours(),
            'minutes' => self::duration()->minutes(),
            'seconds' => self::duration()->seconds(),
            'micros' => self::duration()->microseconds(),
        ], Duration::class, context: [DurationNormalizer::KEY_FORMAT => DurationNormalizer::FORMAT_ARRAY]);

        self::assertInstanceOf(Duration::class, $actual);
        self::assertEquals(self::duration(), $actual);
    }

    public function testNormalizeTimeInterval(): void
    {
        $actual = self::$serializer->normalize(self::duration(), context: [
            DurationNormalizer::KEY_FORMAT => DurationNormalizer::FORMAT_TIME_INTERVAL,
        ]);

        self::assertIsString($actual);
        self::assertEquals(\sprintf(
            '%02d:%02d:%02d.%06d',
            self::duration()->totalHours(),
            self::duration()->minutes(),
            self::duration()->seconds(),
            self::duration()->microseconds(),
        ), $actual);
    }

    public function testDenormalizeTimeInterval(): void
    {
        $actual = self::$serializer->denormalize(\sprintf(
            '%02d:%02d:%02d.%06d',
            self::duration()->totalHours(),
            self::duration()->minutes(),
            self::duration()->seconds(),
            self::duration()->microseconds(),
        ), Duration::class, context: [DurationNormalizer::KEY_FORMAT => DurationNormalizer::FORMAT_TIME_INTERVAL]);

        self::assertInstanceOf(Duration::class, $actual);
        self::assertEquals(self::duration(), $actual);
    }
}
