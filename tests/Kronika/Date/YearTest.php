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

use Kronika\Date\Year;
use Kronika\Duration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class YearTest extends TestCase
{
    public function testBasic(): void
    {
        $year = Year::of(2025);

        $this->assertEquals(2025, $year->number());
        $this->assertTrue($year->is(2025));

        $this->assertNotSame($year, Year::of(2024));
        $this->assertSame($year, Year::of($year));
        $this->assertSame($year, Year::of(2025));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\AssertionError::class);
        Year::of(100000);

        $this->expectException(\AssertionError::class);
        Year::of(-100000);

    }

    public static function comparisonProvider(): array
    {
        return [
            [Year::of(2025), Year::of(2026), -1],
            [Year::of(2025), Year::of(2025), 0],
            [Year::of(2026), Year::of(2025), 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Year $a, Year $b, int $expected): void
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

    #[Depends('testComparison')]
    public function testPrevious(): void
    {
        $current = Year::of(2025);
        $previous = Year::of(2024);

        $result = $current->previous();

        $this->assertEquals($previous->number(), $result->number());
        $this->assertNotSame($current, $result);
        $this->assertSame($previous, $result);
    }

    #[Depends('testComparison')]
    public function testNext(): void
    {
        $current = Year::of(2025);
        $next = Year::of(2026);

        $result = $current->next();

        $this->assertEquals($next->number(), $result->number());
        $this->assertNotSame($current, $result);
        $this->assertSame($next, $result);
    }

    public static function leapProvider(): array
    {
        return [
            [2025, false],
            [2024, true],
            [1900, false],
            [2000, true],
            [2050, false],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('leapProvider')]
    public function testLeap(int $value, bool $expected): void
    {
        $year = Year::of($value);

        $this->assertEquals($expected, $year->isLeap());
        $this->assertEquals($expected ? 366 : 365, $year->length());
        $this->assertEquals(Duration::of(days: $expected ? 366 : 365), $year->duration());
    }
}
