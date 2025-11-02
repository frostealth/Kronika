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

use Kronika\Date;
use Kronika\Extension\Laravel\Eloquent\Casts\AsDate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsDate::class)]
final class DateCastTest extends TestCase
{
    public static function castProvider(): array
    {
        return [
            [new AsDate(), Date::of(2025, 12, 31), '2025-12-31'],
            [new AsDate(), null, null],
            [new AsDate(format: 'd M Y'), Date::of(2000, 06, 25), '25 Jun 2000'],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(AsDate $cast, null|Date $date, null|string $value): void
    {
        static $model = new Foo();
        static $key = 'date';
        static $attributes = [];

        self::assertEquals($date, $cast->get($model, $key, $value, $attributes));
        self::assertEquals($value, $cast->set($model, $key, $date, $attributes));
    }
}
