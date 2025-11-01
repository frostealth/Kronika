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

use Kronika\Extension\Symfony\Normalizer\HourNormalizer;
use Kronika\Extension\Symfony\Normalizer\MinuteNormalizer;
use Kronika\Extension\Symfony\Normalizer\SecondNormalizer;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(HourNormalizer::class)]
#[CoversClass(MinuteNormalizer::class)]
#[CoversClass(SecondNormalizer::class)]
final class TimeUnitNormalizersTest extends TestCase
{
    private static Serializer $serializer;
    private static Hour $hour;
    private static Minute $minute;
    private static Second $second;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$hour = Hour::of(23);
        self::$minute = Minute::of(59);
        self::$second = Second::of(55, 4545);

        self::$serializer = new Serializer(
            normalizers: [new HourNormalizer(), new MinuteNormalizer(), new SecondNormalizer()],
        );
    }

    public function testYearNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$hour);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$hour->value(), $actual);
    }

    public function testYearDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$hour->value(), Hour::class);

        self::assertInstanceOf(Hour::class, $actual);
        self::assertEquals(self::$hour, $actual);
    }

    public function testMonthNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$minute);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$minute->value(), $actual);
    }

    public function testMonthDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$minute->value(), Minute::class);

        self::assertInstanceOf(Minute::class, $actual);
        self::assertEquals(self::$minute, $actual);
    }

    public function testDayOfMonthNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$second);

        self::assertIsNumeric($actual);
        self::assertEquals(self::$second->value(), $actual);
    }

    public function testDayOfMonthDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$second->value(), Second::class);

        self::assertInstanceOf(Second::class, $actual);
        self::assertEquals(self::$second, $actual);
    }
}
