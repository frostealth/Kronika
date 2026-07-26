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
use Kronika\Date\Exception\InvalidDayOfWeek;
use Kronika\OverflowMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
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

        self::assertSame(DayOfWeek::Monday, DayOfWeek::of('monday'));
        self::assertSame(DayOfWeek::Tuesday, DayOfWeek::of('tuesday'));
        self::assertSame(DayOfWeek::Wednesday, DayOfWeek::of('wednesday'));
        self::assertSame(DayOfWeek::Friday, DayOfWeek::of('friday'));
        self::assertSame(DayOfWeek::Saturday, DayOfWeek::of('saturday'));
        self::assertSame(DayOfWeek::Sunday, DayOfWeek::of('sunday'));

        self::assertSame(DayOfWeek::Sunday, DayOfWeek::of(0));
    }

    #[TestWith([8])]
    #[TestWith([-1])]
    #[TestWith(['November'])]
    #[Depends('testBasic')]
    public function testInvalidValue(int|string $value): void
    {
        $this->expectException(InvalidDayOfWeek::class);
        DayOfWeek::of($value);
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
            [DayOfWeek::Sunday, DayOfWeek::Sunday, OverflowMode::Clamp],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('nextProvider')]
    public function testNext(DayOfWeek $day, DayOfWeek $expected, OverflowMode $mode = OverflowMode::Roll): void
    {
        self::assertEquals($expected, $day->next(mode: $mode));
        self::assertSame($expected, $day->next(mode: $mode));
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
            [DayOfWeek::Monday, DayOfWeek::Monday, OverflowMode::Clamp],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('previousProvider')]
    public function testPrevious(DayOfWeek $day, DayOfWeek $expected, OverflowMode $mode = OverflowMode::Roll): void
    {
        self::assertEquals($expected, $day->previous(mode: $mode));
        self::assertSame($expected, $day->previous(mode: $mode));
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
        self::assertTrue($a->is($a));
        self::assertFalse($a->isNot($a));
        self::assertFalse($a->isBefore($a));
        self::assertFalse($a->isAfter($a));
        self::assertTrue($a->isBeforeOrEqualTo($a));
        self::assertTrue($a->isAfterOrEqualTo($a));

        $comparison = $a->compareTo($b);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b));
        self::assertEquals($comparison->greater(), $a->isAfter($b));
        self::assertEquals($comparison->equal(), $a->is($b));
        self::assertEquals($comparison->notEqual(), $a->isNot($b));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b));
    }

    #[TestWith([DayOfWeek::Monday, true])]
    #[TestWith([DayOfWeek::Tuesday, true])]
    #[TestWith([DayOfWeek::Wednesday, true])]
    #[TestWith([DayOfWeek::Thursday, true])]
    #[TestWith([DayOfWeek::Friday, true])]
    #[TestWith([DayOfWeek::Saturday, false])]
    #[TestWith([DayOfWeek::Sunday, false])]
    public function testIsWeekday(DayOfWeek $day, bool $expected): void
    {
        self::assertEquals($expected, $day->isWeekday());
    }

    #[TestWith([DayOfWeek::Monday, false])]
    #[TestWith([DayOfWeek::Tuesday, false])]
    #[TestWith([DayOfWeek::Wednesday, false])]
    #[TestWith([DayOfWeek::Thursday, false])]
    #[TestWith([DayOfWeek::Friday, false])]
    #[TestWith([DayOfWeek::Saturday, true])]
    #[TestWith([DayOfWeek::Sunday, true])]
    public function testIsWeekend(DayOfWeek $day, bool $expected): void
    {
        self::assertEquals($expected, $day->isWeekend());
    }
}
