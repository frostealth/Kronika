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

use Kronika\Duration;
use Kronika\Extension\Laravel\Eloquent\Casts\AsArrayDuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsArrayDuration::class)]
final class ArrayDurationCest extends TestCase
{
    public static function castProvider(): array
    {
        $asArrayDuration = new AsArrayDuration();
        $duration = Duration::of(days: 21, hours: 20, minutes: 30, seconds: 45);

        return [
            [$asArrayDuration, $duration, $duration],
            [$asArrayDuration, $duration->roundToHours(), $duration->roundToHours()],
            [$asArrayDuration, null, null],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(AsArrayDuration $cast, null|Duration $duration, null|Duration $expected): void
    {
        static $model = new Foo();
        static $key = 'duration';
        static $attributes = [];

        self::assertEquals($expected, $cast->get($model, $key, $expected === null ? null : [
            'days' => $duration->days(),
            'hours' => $duration->hours(),
            'minutes' => $duration->minutes(),
            'seconds' => $duration->seconds(),
        ], $attributes));
        self::assertEquals($duration === null ? null : [
            'days' => $expected->days(),
            'hours' => $expected->hours(),
            'minutes' => $expected->minutes(),
            'seconds' => $expected->seconds(),
        ], $cast->set($model, $key, $duration, $attributes));
    }
}
