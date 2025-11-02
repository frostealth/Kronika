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

use Kronika\Extension\Laravel\Eloquent\Casts\AsTime;
use Kronika\Precision;
use Kronika\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsTime::class)]
final class TimeCastTest extends TestCase
{
    public static function castProvider(): array
    {
        return [
            [new AsTime(), Time::midnight(), '00:00:00.000000'],
            [new AsTime(), Time::endOfDay(), '23:59:59.999999'],
            [new AsTime(), null, null],
            [new AsTime(format: 'H:i:s'), Time::endOfDay(), '23:59:59', Precision::Second],
            [new AsTime(format: 'H:i'), Time::endOfDay(), '23:59', Precision::Minute],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(AsTime $cast, null|Time $time, null|string $value, Precision $precision = Precision::Micro): void
    {
        static $model = new Foo();
        static $key = 'time';
        static $attributes = [];

        $expected = match($precision) {
            Precision::Micro => $time,
            Precision::Second => $time->resetMicro(),
            Precision::Minute => $time->resetSecond(),
        };

        self::assertEquals($expected, $cast->get($model, $key, $value, $attributes));
        self::assertEquals($value, $cast->set($model, $key, $time, $attributes));
    }
}
