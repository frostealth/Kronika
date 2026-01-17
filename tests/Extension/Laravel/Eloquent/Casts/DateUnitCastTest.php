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
use Kronika\Date\DateUnit;
use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\DayOfYear;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Extension\Laravel\Eloquent\Casts\AsDayOfMonth;
use Kronika\Extension\Laravel\Eloquent\Casts\AsDayOfWeek;
use Kronika\Extension\Laravel\Eloquent\Casts\AsDayOfYear;
use Kronika\Extension\Laravel\Eloquent\Casts\AsMonth;
use Kronika\Extension\Laravel\Eloquent\Casts\AsYear;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsYear::class)]
#[CoversClass(AsMonth::class)]
#[CoversClass(AsDayOfMonth::class)]
#[CoversClass(AsDayOfWeek::class)]
#[CoversClass(AsDayOfYear::class)]
final class DateUnitCastTest extends TestCase
{
    public static function castProvider(): array
    {
        $asYear = new AsYear();
        $asMonth = new AsMonth();
        $asDayOfMonth = new AsDayOfMonth();
        $asDayOfWeek = new AsDayOfWeek();
        $asDayOfYear = new AsDayOfYear();

        return [
            [$asYear, Year::of(2025)],
            [$asYear, null],

            [$asMonth, Month::April],
            [$asMonth, null],

            [$asDayOfMonth, DayOfMonth::of(25)],
            [$asDayOfMonth, null],

            [$asDayOfWeek, DayOfWeek::Sunday],
            [$asDayOfWeek, null],

            [$asDayOfYear, DayOfYear::of(245)],
            [$asDayOfYear, null],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(CastsAttributes $cast, ?DateUnit $unit): void
    {
        static $model = new Foo();
        static $key = 'key';
        static $attributes = [];

        self::assertEquals($unit, $cast->get($model, $key, $unit?->number(), $attributes));
        self::assertEquals($unit?->number(), $cast->set($model, $key, $unit, $attributes));
    }
}
