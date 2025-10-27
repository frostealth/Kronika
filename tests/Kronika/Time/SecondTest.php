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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class SecondTest extends TestCase
{
    public function testBasic(): void
    {
        $second = Second::of(45, 7788);

        $this->assertEquals(45, $second->second());
        $this->assertEquals(7788, $second->microsecond());
        $this->assertEquals(45.007788, $second->value());

        $this->assertTrue($second->is(45.007788));
        $this->assertTrue($second->is('45.007788'));
        $this->assertFalse($second->is(45));
        $this->assertFalse($second->is('45'));
        $this->assertFalse($second->is(40));
        $this->assertFalse($second->is('40'));
        $this->assertFalse($second->is(40.000031));
        $this->assertFalse($second->is('40.000031'));

        $this->assertNotSame($second, Second::of(50, 7788));
        $this->assertSame($second, Second::of(45, 7788));

        $this->assertSame($second, Second::of($second));
        $this->assertSame($second, Second::of($second, 7788));
        $this->assertNotEquals($second, Second::of($second, 50));
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
        $this->assertEquals(0, $actual->second());
        $this->assertEquals(0, $actual->microsecond());
        $this->assertEquals(0.0, $actual->value());
        $this->assertTrue($actual->isZero());
        $this->assertFalse($actual->isLast());

        unset($actual);
        $expected = Second::zero();
        $actual = Second::of(0);
        $this->assertSame($expected, $actual);
        $this->assertSame(Second::zero(), $actual);
        $this->assertTrue($actual->isZero());
        $this->assertFalse($actual->isLast());

        unset($expected, $actual);
        $this->assertSame(Second::of(0), Second::zero());
    }

    #[Depends('testBasic')]
    public function testLast(): void
    {
        $actual = Second::last();
        $this->assertEquals(59, $actual->second());
        $this->assertEquals(999999, $actual->microsecond());
        $this->assertEquals(59.999999, $actual->value());
        $this->assertFalse($actual->isZero());
        $this->assertTrue($actual->isLast());

        unset($actual);
        $expected = Second::last();
        $actual = Second::of(59, 999999);
        $this->assertSame($expected, $actual);
        $this->assertSame(Second::last(), $actual);
        $this->assertFalse($actual->isZero());
        $this->assertTrue($actual->isLast());

        unset($expected, $actual);
        $this->assertSame(Second::of(59, 999999), Second::last());
    }

    #[Depends('testBasic')]
    public function testResetMicrosecond(): void
    {
        $second = Second::of(45, 7788);

        $second = $second->resetMicro();

        $this->assertEquals(45, $second->second());
        $this->assertEquals(0, $second->microsecond());
        $this->assertEquals(45.0, $second->value());
        $this->assertTrue($second->is(45));
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
        $this->assertEquals($expected, (string)$second);
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
        $this->assertTrue($a->isEqualTo($a));
        $this->assertFalse($a->isNotEqualTo($a));
        $this->assertFalse($a->isBefore($a));
        $this->assertFalse($a->isAfter($a));
        $this->assertTrue($a->isBeforeOrEqualTo($a));
        $this->assertTrue($a->isAfterOrEqualTo($a));

        $comparison = $a->compareTo($b);
        $this->assertEquals($expected, $comparison->value());
        $this->assertEquals($comparison->less(), $a->isBefore($b));
        $this->assertEquals($comparison->greater(), $a->isAfter($b));
        $this->assertEquals($comparison->equal(), $a->isEqualTo($b));
        $this->assertEquals($comparison->notEqual(), $a->isNotEqualTo($b));
        $this->assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b));
        $this->assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b));
    }
}
