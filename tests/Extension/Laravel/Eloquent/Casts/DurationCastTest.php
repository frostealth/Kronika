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
use Kronika\Extension\Laravel\Eloquent\Casts\AsDuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsDuration::class)]
final class DurationCastTest extends TestCase
{
    public static function castProvider(): array
    {
        $duration = Duration::of(days: 21, hours: 20, minutes: 30, seconds: 45);

        return [
            [new AsDuration(), $duration, $duration->inSeconds(), $duration],
            [new AsDuration(), null, null, null],
            [new AsDuration(format: AsDuration::FORMAT_IN_MINUTES), $duration, $duration->inMinutes(), $duration->roundToMinutes()],
            [new AsDuration(format: AsDuration::FORMAT_IN_HOURS), $duration, $duration->inHours(), $duration->roundToHours()],
            [new AsDuration(format: AsDuration::FORMAT_IN_DAYS), $duration, $duration->inDays(), $duration->roundToDays()],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(AsDuration $cast, null|Duration $duration, null|int $value, null|Duration $expected): void
    {
        static $model = new Foo();
        static $key = 'duration';
        static $attributes = [];

        self::assertEquals($expected, $cast->get($model, $key, $value, $attributes));
        self::assertEquals($value, $cast->set($model, $key, $duration, $attributes));
    }
}
