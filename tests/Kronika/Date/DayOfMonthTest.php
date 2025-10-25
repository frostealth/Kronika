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

namespace Kronika\Tests\Date;

use Kronika\Date\DayOfMonth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DayOfMonthTest extends TestCase
{
    public function testBasic(): void
    {
        $day = DayOfMonth::of(15);

        $this->assertEquals(15, $day->number());
        $this->assertTrue($day->is(15));

        $this->assertNotSame($day, DayOfMonth::of(2));
        $this->assertSame($day, DayOfMonth::of(15));
        $this->assertSame($day, DayOfMonth::of($day));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\AssertionError::class);
        DayOfMonth::of(32);

        $this->expectException(\AssertionError::class);
        DayOfMonth::of(1212);

        $this->expectException(\AssertionError::class);
        DayOfMonth::of(0);

        $this->expectException(\AssertionError::class);
        DayOfMonth::of(-1);
    }

    public static function toStringProvider(): array
    {
        return [
            [DayOfMonth::of(1), '01'],
            [DayOfMonth::of(6), '06'],
            [DayOfMonth::of(12), '12'],
            [DayOfMonth::of(25), '25'],
            [DayOfMonth::of(31), '31'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('toStringProvider')]
    public function testToString(DayOfMonth $day, string $expected): void
    {
        $this->assertEquals($expected, (string)$day);
    }

    public static function comparisonProvider(): array
    {
        return [
            [DayOfMonth::of(10), DayOfMonth::of(20), -1],
            [DayOfMonth::of(10), DayOfMonth::of(10), 0],
            [DayOfMonth::of(20), DayOfMonth::of(10), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(DayOfMonth $a, DayOfMonth $b, int $expected): void
    {
        $this->assertTrue($a->isEqualTo($a));
        $this->assertFalse($a->isNotEqualTo($a));
        $this->assertFalse($a->isBefore($a));
        $this->assertFalse($a->isAfter($a));
        $this->assertTrue($a->isBeforeOrEqual($a));
        $this->assertTrue($a->isAfterOrEqual($a));

        $comparison = $a->compareTo($b);
        $this->assertEquals($expected, $comparison->value());
        $this->assertEquals($comparison->less(), $a->isBefore($b));
        $this->assertEquals($comparison->greater(), $a->isAfter($b));
        $this->assertEquals($comparison->equal(), $a->isEqualTo($b));
        $this->assertEquals($comparison->notEqual(), $a->isNotEqualTo($b));
        $this->assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqual($b));
        $this->assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqual($b));
    }
}
