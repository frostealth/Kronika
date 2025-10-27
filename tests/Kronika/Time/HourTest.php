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

use Kronika\Time\Hour;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class HourTest extends TestCase
{
    public function testBasic(): void
    {
        $hour = Hour::of(12);

        $this->assertEquals(12, $hour->value());
        $this->assertTrue($hour->is(12));

        $this->assertNotSame($hour, Hour::of(2));
        $this->assertSame($hour, Hour::of(12));
        $this->assertSame($hour, Hour::of($hour));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\AssertionError::class);
        Hour::of(24);

        $this->expectException(\AssertionError::class);
        Hour::of(1212);

        $this->expectException(\AssertionError::class);
        Hour::of(-1);
    }

    #[Depends('testBasic')]
    public function testZero(): void
    {
        $actual = Hour::zero();
        $this->assertEquals(0, $actual->value());
        $this->assertTrue($actual->isZero());
        $this->assertFalse($actual->isLast());

        unset($actual);
        $expected = Hour::zero();
        $actual = Hour::of(0);
        $this->assertSame($expected, $actual);
        $this->assertSame(Hour::zero(), $actual);
        $this->assertTrue($actual->isZero());
        $this->assertFalse($actual->isLast());

        unset($expected, $actual);
        $this->assertSame(Hour::of(0), Hour::zero());
    }

    #[Depends('testBasic')]
    public function testLast(): void
    {
        $actual = Hour::last();
        $this->assertEquals(23, $actual->value());
        $this->assertFalse($actual->isZero());
        $this->assertTrue($actual->isLast());

        unset($actual);
        $expected = Hour::last();
        $actual = Hour::of(23);
        $this->assertSame($expected, $actual);
        $this->assertSame(Hour::last(), $actual);
        $this->assertFalse($actual->isZero());
        $this->assertTrue($actual->isLast());

        unset($expected, $actual);
        $this->assertSame(Hour::of(23), Hour::last());
    }

    public static function toStringProvider(): array
    {
        return [
            [Hour::of(0), '00'],
            [Hour::of(6), '06'],
            [Hour::of(12), '12'],
            [Hour::of(23), '23'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toStringProvider')]
    public function testToString(Hour $hour, string $expected): void
    {
        $this->assertEquals($expected, (string)$hour);
    }

    public static function comparisonProvider(): array
    {
        return [
            [Hour::of(12), Hour::of(23), -1],
            [Hour::of(12), Hour::of(12), 0],
            [Hour::of(23), Hour::of(12), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Hour $a, Hour $b, int $expected): void
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
