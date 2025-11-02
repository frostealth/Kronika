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

namespace Kronika\Tests\Time;

use Kronika\Time\Second;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(Second::class)]
final class SecondTest extends TestCase
{
    public function testBasic(): void
    {
        $second = Second::of(45, 7788);

        self::assertEquals(45, $second->second());
        self::assertEquals(7788, $second->microsecond());
        self::assertEquals(45.007788, $second->value());

        self::assertNotSame($second, Second::of(50, 7788));
        self::assertSame($second, Second::of(45, 7788));

        self::assertSame($second, Second::of($second));
        self::assertSame($second, Second::of($second, 7788));
        self::assertNotEquals($second, Second::of($second, 50));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\AssertionError::class);
        Second::of(60);

        $this->expectException(\AssertionError::class);
        Second::of(30, 1_000_000);

        $this->expectException(\AssertionError::class);
        Second::of(1212);

        $this->expectException(\AssertionError::class);
        Second::of(-1);

        $this->expectException(\AssertionError::class);
        Second::of(30, -75);
    }

    #[Depends('testBasic')]
    public function testZero(): void
    {
        $actual = Second::zero();
        self::assertEquals(0, $actual->second());
        self::assertEquals(0, $actual->microsecond());
        self::assertEquals(0.0, $actual->value());
        self::assertTrue($actual->isZero());
        self::assertFalse($actual->isLast());

        unset($actual);
        $expected = Second::zero();
        $actual = Second::of(0);
        self::assertSame($expected, $actual);
        self::assertSame(Second::zero(), $actual);
        self::assertTrue($actual->isZero());
        self::assertFalse($actual->isLast());

        unset($expected, $actual);
        self::assertSame(Second::of(0), Second::zero());
    }

    #[Depends('testBasic')]
    public function testLast(): void
    {
        $actual = Second::last();
        self::assertEquals(59, $actual->second());
        self::assertEquals(999999, $actual->microsecond());
        self::assertEquals(59.999999, $actual->value());
        self::assertFalse($actual->isZero());
        self::assertTrue($actual->isLast());

        unset($actual);
        $expected = Second::last();
        $actual = Second::of(59, 999999);
        self::assertSame($expected, $actual);
        self::assertSame(Second::last(), $actual);
        self::assertFalse($actual->isZero());
        self::assertTrue($actual->isLast());

        unset($expected, $actual);
        self::assertSame(Second::of(59, 999999), Second::last());
    }

    #[Depends('testBasic')]
    public function testResetMicrosecond(): void
    {
        $second = Second::of(45, 7788);

        $second = $second->resetMicro();

        self::assertEquals(45, $second->second());
        self::assertEquals(0, $second->microsecond());
        self::assertEquals(45.0, $second->value());
    }

    public static function toStringProvider(): array
    {
        return [
            [Second::of(0), '00.000000'],
            [Second::of(0, 5544), '00.005544'],
            [Second::of(06), '06.000000'],
            [Second::of(6, 9864), '06.009864'],
            [Second::of(30), '30.000000'],
            [Second::of(23), '23.000000'],
            [Second::of(59, 999999), '59.999999'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toStringProvider')]
    public function testToString(Second $second, string $expected): void
    {
        self::assertEquals($expected, (string)$second);
    }

    public static function comparisonProvider(): array
    {
        return [
            [Second::of(0), Second::of(0, 1), -1],
            [Second::of(10), Second::of(20), -1],
            [Second::of(0), Second::of(0), 0],
            [Second::of(0, 9977), Second::of(0, 9977), 0],
            [Second::of(45, 348291), Second::of(45, 348290), 1],
            [Second::of(59), Second::of(35), 1],
            [Second::of(59, 999999), Second::of(59), 1],
            [Second::of(59, 999999), Second::of(59, 999998), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Second $a, Second $b, int $expected): void
    {
        self::assertTrue($a->isEqualTo($a));
        self::assertFalse($a->isNotEqualTo($a));
        self::assertFalse($a->isBefore($a));
        self::assertFalse($a->isAfter($a));
        self::assertTrue($a->isBeforeOrEqualTo($a));
        self::assertTrue($a->isAfterOrEqualTo($a));

        $comparison = $a->compareTo($b);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b));
        self::assertEquals($comparison->greater(), $a->isAfter($b));
        self::assertEquals($comparison->equal(), $a->isEqualTo($b));
        self::assertEquals($comparison->notEqual(), $a->isNotEqualTo($b));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b));
    }
}
