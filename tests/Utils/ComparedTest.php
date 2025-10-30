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

namespace Kronika\Utils\Tests;

use Kronika\Utils\Compared;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Compared::class)]
final class ComparedTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    #[TestWith([self::LESS])]
    #[TestWith([self::EQUAL])]
    #[TestWith([self::GREATER])]
    public function testBasic(int $value): void
    {
        $actual = Compared::of($value);

        self::assertEquals($value, $actual->value());
        self::assertEquals($value === self::LESS, $actual->less());
        self::assertEquals($value === self::LESS || $value === self::EQUAL, $actual->lessOrEqual());
        self::assertEquals($value === self::EQUAL, $actual->equal());
        self::assertEquals($value !== self::EQUAL, $actual->notEqual());
        self::assertEquals($value === self::GREATER || $value === self::EQUAL, $actual->greaterOrEqual());
        self::assertEquals($value === self::GREATER, $actual->greater());
    }
}
