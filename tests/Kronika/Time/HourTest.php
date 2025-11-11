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

use Kronika\Time\Exception\InvalidHour;
use Kronika\Time\Hour;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Hour::class)]
final class HourTest extends TestCase
{
    public function testBasic(): void
    {
        $hour = Hour::of(12);

        self::assertEquals(12, $hour->value());
        self::assertNotSame($hour, Hour::of(2));
        self::assertSame($hour, Hour::of(12));
        self::assertSame($hour, Hour::of($hour));
    }

    #[TestWith([24])]
    #[TestWith([-1])]
    #[Depends('testBasic')]
    public function testInvalidValue(int $value): void
    {
        $this->expectException(InvalidHour::class);
        Hour::of($value);
    }

    #[Depends('testBasic')]
    public function testZero(): void
    {
        $actual = Hour::zero();
        self::assertEquals(0, $actual->value());
        self::assertTrue($actual->isZero());
        self::assertFalse($actual->isLast());

        unset($actual);
        $expected = Hour::zero();
        $actual = Hour::of(0);
        self::assertSame($expected, $actual);
        self::assertSame(Hour::zero(), $actual);
        self::assertTrue($actual->isZero());
        self::assertFalse($actual->isLast());

        unset($expected, $actual);
        self::assertSame(Hour::of(0), Hour::zero());
    }

    #[Depends('testBasic')]
    public function testLast(): void
    {
        $actual = Hour::last();
        self::assertEquals(23, $actual->value());
        self::assertFalse($actual->isZero());
        self::assertTrue($actual->isLast());

        unset($actual);
        $expected = Hour::last();
        $actual = Hour::of(23);
        self::assertSame($expected, $actual);
        self::assertSame(Hour::last(), $actual);
        self::assertFalse($actual->isZero());
        self::assertTrue($actual->isLast());

        unset($expected, $actual);
        self::assertSame(Hour::of(23), Hour::last());
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
        self::assertEquals($expected, (string)$hour);
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

    #[TestWith([1, 0])]
    #[TestWith([1, 0, 'rolling' => false])]
    #[TestWith([1, 0, 'rolling' => true])]
    #[TestWith([9, 8])]
    #[TestWith([15, 14])]
    #[TestWith([20, 19])]
    #[TestWith([22, 21])]
    #[TestWith([23, 22])]
    #[TestWith([23, 22, 'rolling' => false])]
    #[TestWith([23, 22, 'rolling' => true])]
    #[Depends('testBasic')]
    public function testPrevious(int $hour, int $expected, bool $rolling = false): void
    {
        self::assertEquals(Hour::of($expected), Hour::of($hour)->previous(rolling: $rolling));
    }

    #[TestWith([1, 2])]
    #[TestWith([1, 2, 'rolling' => false])]
    #[TestWith([1, 2, 'rolling' => true])]
    #[TestWith([9, 10])]
    #[TestWith([15, 16])]
    #[TestWith([20, 21])]
    #[TestWith([22, 23])]
    #[TestWith([23, 23])]
    #[TestWith([23, 23, 'rolling' => false])]
    #[TestWith([23, 0, 'rolling' => true])]
    #[Depends('testBasic')]
    public function testNext(int $hour, int $expected, bool $rolling = false): void
    {
        self::assertEquals(Hour::of($expected), Hour::of($hour)->next(rolling: $rolling));
    }
}
