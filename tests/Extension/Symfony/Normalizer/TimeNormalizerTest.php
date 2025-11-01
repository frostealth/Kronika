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

use Kronika\Extension\Symfony\Normalizer\TimeNormalizer;
use Kronika\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(TimeNormalizer::class)]
final class TimeNormalizerTest extends TestCase
{
    private static Serializer $serializer;
    private static Time $time;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$time = Time::endOfDay();
        self::$serializer = new Serializer(
            normalizers: [new TimeNormalizer(format: TimeNormalizer::DEFAULT_FORMAT)],
        );
    }

    public function testNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$time);

        self::assertIsString($actual);
        self::assertEquals(self::$time->format(TimeNormalizer::DEFAULT_FORMAT), $actual);
    }

    public function testNormalizeWithCustomFormat(): void
    {
        $format = 'H/i/s';
        $actual = self::$serializer->normalize(self::$time, context: [
            TimeNormalizer::KEY_FORMAT => $format,
        ]);

        self::assertIsString($actual);
        self::assertEquals(self::$time->format($format), $actual);
    }

    public function testDenormalize(): void
    {
        $format = TimeNormalizer::DEFAULT_FORMAT;
        $actual = self::$serializer->denormalize(self::$time->format($format), Time::class);

        self::assertInstanceOf(Time::class, $actual);
        self::assertEquals(self::$time, $actual);
    }

    public function testDenormalizeWithCustomFormat(): void
    {
        $format = 'H/i/s';
        $actual = self::$serializer->denormalize(self::$time->format($format), Time::class, context: [
            TimeNormalizer::KEY_FORMAT => $format,
        ]);

        self::assertInstanceOf(Time::class, $actual);
        self::assertEquals(self::$time->resetMicro(), $actual);
    }
}
