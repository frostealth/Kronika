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

use Kronika\Extension\Symfony\Serializer\Normalizer\InstantNormalizer;
use Kronika\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(InstantNormalizer::class)]
final class InstantNormalizerTest extends TestCase
{
    private static Serializer $serializer;
    private static Instant $instant;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$instant = Instant::of(1234567890, 999);
        self::$serializer = new Serializer(
            normalizers: [new InstantNormalizer()],
        );
    }

    public function testNormalize(): void
    {
        $actual = self::$serializer->normalize(self::$instant);

        self::assertIsFloat($actual);
        self::assertEquals(self::$instant->value(), $actual);
    }

    public function testDenormalize(): void
    {
        $actual = self::$serializer->denormalize(self::$instant->value(), Instant::class);

        self::assertInstanceOf(Instant::class, $actual);
        self::assertEquals(self::$instant, $actual);
    }
}
