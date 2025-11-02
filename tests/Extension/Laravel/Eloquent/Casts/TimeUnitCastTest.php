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

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Kronika\Extension\Laravel\Eloquent\Casts\AsHour;
use Kronika\Extension\Laravel\Eloquent\Casts\AsMinute;
use Kronika\Extension\Laravel\Eloquent\Casts\AsSecond;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Time\TimeUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsHour::class)]
#[CoversClass(AsMinute::class)]
#[CoversClass(AsSecond::class)]
final class TimeUnitCastTest extends TestCase
{
    public static function castProvider(): array
    {
        $asHour = new AsHour();
        $asMinute = new AsMinute();
        $asSecond = new AsSecond();

        return [
            [$asHour, Hour::last()],
            [$asHour, Hour::zero()],
            [$asHour, null],

            [$asMinute, Minute::last()],
            [$asMinute, Minute::zero()],
            [$asMinute, null],

            [$asSecond, Second::last()],
            [$asSecond, Second::zero()],
            [$asSecond, null],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(CastsAttributes $cast, ?TimeUnit $unit): void
    {
        static $model = new Foo();
        static $key = 'key';
        static $attributes = [];

        self::assertEquals($unit, $cast->get($model, $key, $unit?->value(), $attributes));
        self::assertEquals($unit?->value(), $cast->set($model, $key, $unit, $attributes));
    }
}
