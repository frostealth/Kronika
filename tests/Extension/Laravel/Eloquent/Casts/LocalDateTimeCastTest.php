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

namespace Kronika\Extension\Tests\Laravel\Eloquent\Casts;

use Kronika\Extension\Laravel\Eloquent\Casts\AsLocalDateTime;
use Kronika\LocalDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsLocalDateTime::class)]
final class LocalDateTimeCastTest extends TestCase
{
    public static function castProvider(): array
    {
        $datetime = LocalDateTime::parse('2025-12-31 12:30:45.999999');

        return [
            [new AsLocalDateTime(), $datetime, 'Y-m-d\TH:i:s.u'],
            [new AsLocalDateTime(), null, 'Y-m-d\TH:i:s.u'],
            [new AsLocalDateTime($format = 'Y-m-d H:i:s'), $datetime, $format],
            [new AsLocalDateTime($format = 'Y-m-d\TH:i'), $datetime, $format],
        ];
    }

    #[DataProvider('castProvider')]
    public static function testCast(AsLocalDateTime $cast, null|LocalDateTime $datetime, string $format): void
    {
        static $model = new Foo();
        static $key = 'localDateTime';
        static $attributes = [];

        $value = $datetime?->format($format);
        $expected = $value === null ? null : LocalDateTime::ofFormat($format, $value);

        self::assertEquals($expected, $cast->get($model, $key, $value, $attributes));
        self::assertEquals($value, $cast->set($model, $key, $datetime, $attributes));
    }
}
