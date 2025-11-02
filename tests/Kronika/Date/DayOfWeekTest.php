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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(DayOfWeek::class)]
final class DayOfWeekTest extends TestCase
{
    public function testBasic(): void
    {
        $day = DayOfWeek::of(3);

        self::assertEquals(3, $day->number());
        self::assertNotSame($day, DayOfWeek::of(2));
        self::assertSame($day, DayOfWeek::of(3));

        self::assertSame(DayOfWeek::Monday, DayOfWeek::of(1));
        self::assertSame(DayOfWeek::Tuesday, DayOfWeek::of(2));
        self::assertSame(DayOfWeek::Wednesday, DayOfWeek::of(3));
        self::assertSame(DayOfWeek::Thursday, DayOfWeek::of(4));
        self::assertSame(DayOfWeek::Friday, DayOfWeek::of(5));
        self::assertSame(DayOfWeek::Saturday, DayOfWeek::of(6));
        self::assertSame(DayOfWeek::Sunday, DayOfWeek::of(7));

        self::assertSame(DayOfWeek::Sunday, DayOfWeek::of(0));
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
        self::assertEquals($expected, $day->name());
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
        self::assertSame($expected, $day->next());
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
        self::assertSame($expected, $day->previous());
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
