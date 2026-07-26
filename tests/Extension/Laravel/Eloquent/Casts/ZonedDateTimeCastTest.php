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

use Kronika\Extension\Laravel\Eloquent\Casts\AsZonedDateTime;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsZonedDateTime::class)]
final class ZonedDateTimeCastTest extends TestCase
{
    public static function castProvider(): array
    {
        $datetime = ZonedDateTime::parse('2025-12-31 12:30:45.999999 +01:30');

        return [
            [new AsZonedDateTime(), $datetime, 'Y-m-d\TH:i:s.uP'],
            [new AsZonedDateTime(), null, 'Y-m-d\TH:i:s.uP'],
            [new AsZonedDateTime($format = 'Y-m-d H:i:s T'), $datetime, $format],
            [new AsZonedDateTime($format = 'Y-m-d\TH:iP'), $datetime, $format],
            [new AsZonedDateTime($format = 'Y-m-d H:i:s O'), $datetime, $format],
        ];
    }

    #[DataProvider('castProvider')]
    public static function testCast(AsZonedDateTime $cast, null|ZonedDateTime $datetime, string $format): void
    {
        static $model = new Foo();
        static $key = 'zonedDateTime';
        static $attributes = [];

        $value = $datetime?->format($format);
        $expected = $value === null ? null : ZonedDateTime::fromFormat($format, $value);

        self::assertEquals($expected, $cast->get($model, $key, $value, $attributes));
        self::assertEquals($value, $cast->set($model, $key, $datetime, $attributes));
    }
}
