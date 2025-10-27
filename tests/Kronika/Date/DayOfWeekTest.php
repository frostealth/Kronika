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

use Kronika\Date\DayOfWeek;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DayOfWeekTest extends TestCase
{
    public function testBasic(): void
    {
        $day = DayOfWeek::of(3);

        $this->assertEquals(3, $day->number());
        $this->assertTrue($day->is(3));
        $this->assertFalse($day->is(5));

        $this->assertNotSame($day, DayOfWeek::of(2));
        $this->assertSame($day, DayOfWeek::of(3));

        $this->assertSame(DayOfWeek::Monday, DayOfWeek::of(1));
        $this->assertSame(DayOfWeek::Tuesday, DayOfWeek::of(2));
        $this->assertSame(DayOfWeek::Wednesday, DayOfWeek::of(3));
        $this->assertSame(DayOfWeek::Thursday, DayOfWeek::of(4));
        $this->assertSame(DayOfWeek::Friday, DayOfWeek::of(5));
        $this->assertSame(DayOfWeek::Saturday, DayOfWeek::of(6));
        $this->assertSame(DayOfWeek::Sunday, DayOfWeek::of(7));

        $this->assertSame(DayOfWeek::Sunday, DayOfWeek::of(0));
    }

    #[Depends('testBasic')]
    public function testInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        DayOfWeek::of(8);

        $this->expectException(\ValueError::class);
        DayOfWeek::of(1212);

        $this->expectException(\ValueError::class);
        DayOfWeek::of(-1);
    }

    public static function nameProvider(): array
    {
        return [
            [DayOfWeek::Monday, 'Monday'],
            [DayOfWeek::Tuesday, 'Tuesday'],
            [DayOfWeek::Wednesday, 'Wednesday'],
            [DayOfWeek::Thursday, 'Thursday'],
            [DayOfWeek::Friday, 'Friday'],
            [DayOfWeek::Saturday, 'Saturday'],
            [DayOfWeek::Sunday, 'Sunday'],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('nameProvider')]
    public function testName(DayOfWeek $day, string $expected): void
    {
        $this->assertEquals($expected, $day->name());
    }

    public static function nextProvider(): array
    {
        return [
            [DayOfWeek::Monday, DayOfWeek::Tuesday],
            [DayOfWeek::Tuesday, DayOfWeek::Wednesday],
            [DayOfWeek::Wednesday, DayOfWeek::Thursday],
            [DayOfWeek::Thursday, DayOfWeek::Friday],
            [DayOfWeek::Friday, DayOfWeek::Saturday],
            [DayOfWeek::Saturday, DayOfWeek::Sunday],
            [DayOfWeek::Sunday, DayOfWeek::Monday],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('nextProvider')]
    public function testNext(DayOfWeek $day, DayOfWeek $expected): void
    {
        $this->assertSame($expected, $day->next());
    }

    public static function previousProvider(): array
    {
        return [
            [DayOfWeek::Sunday, DayOfWeek::Saturday],
            [DayOfWeek::Saturday, DayOfWeek::Friday],
            [DayOfWeek::Friday, DayOfWeek::Thursday],
            [DayOfWeek::Thursday, DayOfWeek::Wednesday],
            [DayOfWeek::Wednesday, DayOfWeek::Tuesday],
            [DayOfWeek::Tuesday, DayOfWeek::Monday],
            [DayOfWeek::Monday, DayOfWeek::Sunday],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('previousProvider')]
    public function testPrevious(DayOfWeek $day, DayOfWeek $expected): void
    {
        $this->assertSame($expected, $day->previous());
    }

    public static function comparisonProvider(): array
    {
        return [
            [DayOfWeek::Wednesday, DayOfWeek::Friday, -1],
            [DayOfWeek::Friday, DayOfWeek::Friday, 0],
            [DayOfWeek::Sunday, DayOfWeek::Friday, 1],
            [DayOfWeek::Monday, DayOfWeek::Sunday, -1],
            [DayOfWeek::Sunday, DayOfWeek::Monday, 1],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(DayOfWeek $a, DayOfWeek $b, int $expected): void
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
