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

use Kronika\Time\Minute;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class MinuteTest extends TestCase
{
    public function testBasic(): void
    {
        $minute = Minute::of(35);

        $this->assertEquals(35, $minute->value());
        $this->assertTrue($minute->is(35));

        $this->assertNotSame($minute, Minute::of(2));
        $this->assertSame($minute, Minute::of($minute));
        $this->assertSame($minute, Minute::of(35));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\AssertionError::class);
        Minute::of(60);

        $this->expectException(\AssertionError::class);
        Minute::of(1212);

        $this->expectException(\AssertionError::class);
        Minute::of(0);

        $this->expectException(\AssertionError::class);
        Minute::of(-1);
    }

    #[Depends('testBasic')]
    public function testZero(): void
    {
        $actual = Minute::zero();
        $this->assertEquals(0, $actual->value());
        $this->assertTrue($actual->isZero());
        $this->assertFalse($actual->isLast());

        unset($actual);
        $expected = Minute::zero();
        $actual = Minute::of(0);
        $this->assertSame($expected, $actual);
        $this->assertSame(Minute::zero(), $actual);
        $this->assertTrue($actual->isZero());
        $this->assertFalse($actual->isLast());

        unset($expected, $actual);
        $this->assertSame(Minute::of(0), Minute::zero());
    }

    #[Depends('testBasic')]
    public function testLast(): void
    {
        $actual = Minute::last();
        $this->assertEquals(59, $actual->value());
        $this->assertFalse($actual->isZero());
        $this->assertTrue($actual->isLast());

        unset($actual);
        $expected = Minute::last();
        $actual = Minute::of(59);
        $this->assertSame($expected, $actual);
        $this->assertSame(Minute::last(), $actual);
        $this->assertFalse($actual->isZero());
        $this->assertTrue($actual->isLast());

        unset($expected, $actual);
        $this->assertSame(Minute::of(59), Minute::last());
    }

    public static function toStringProvider(): array
    {
        return [
            [Minute::of(0), '00'],
            [Minute::of(6), '06'],
            [Minute::of(12), '12'],
            [Minute::of(35), '35'],
            [Minute::of(59), '59'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toStringProvider')]
    public function testToString(Minute $minute, string $expected): void
    {
        $this->assertEquals($expected, (string)$minute);
    }

    public static function comparisonProvider(): array
    {
        return [
            [Minute::of(12), Minute::of(23), -1],
            [Minute::of(12), Minute::of(12), 0],
            [Minute::of(23), Minute::of(12), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Minute $a, Minute $b, int $expected): void
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
