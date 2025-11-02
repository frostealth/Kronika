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

use Kronika\Extension\Laravel\Eloquent\Casts\AsInstant;
use Kronika\Instant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AsInstant::class)]
final class InstantCastTest extends TestCase
{
    public static function castProvider(): array
    {
        $asInstant = new AsInstant();

        return [
            [$asInstant, Instant::of(1234567890), 1234567890.0],
            [$asInstant, Instant::of(1234567890, 5678), 1234567890.005678],
            [$asInstant, null, null],
        ];
    }

    #[DataProvider('castProvider')]
    public function testCast(AsInstant $cast, null|Instant $instant, null|float $value): void
    {
        static $model = new Foo();
        static $key = 'instant';
        static $attributes = [];

        self::assertEquals($instant, $cast->get($model, $key, $value, $attributes));
        self::assertEquals($value, $cast->set($model, $key, $instant, $attributes));
    }
}
