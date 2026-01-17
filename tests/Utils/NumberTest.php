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

namespace Kronika\Utils\Tests;

use Kronika\Utils\Number;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class NumberTest extends TestCase
{
    /**
     * @param array{int, int} $args
     * @param array{integer: int, fraction: int, value: string} $expected
     */
    #[TestWith([[10, 0], ['integer' => 10, 'fraction' => 0, 'value' => '10.000000']])]
    #[TestWith([[10, 11], ['integer' => 10, 'fraction' => 11, 'value' => '10.000011']])]
    #[TestWith([[5, 111_222], ['integer' => 5, 'fraction' => 111_222, 'value' => '5.111222']])]
    #[TestWith([[55, 999_999], ['integer' => 55, 'fraction' => 999_999, 'value' => '55.999999']])]
    #[TestWith([[55, 1_000_000], ['integer' => 56, 'fraction' => 0, 'value' => '56.000000']])]
    #[TestWith([[55, 1_000_001], ['integer' => 56, 'fraction' => 1, 'value' => '56.000001']])]
    #[TestWith([[55, 2_000_001], ['integer' => 57, 'fraction' => 1, 'value' => '57.000001']])]
    #[TestWith([[0, 2_000_000], ['integer' => 2, 'fraction' => 0, 'value' => '2.000000']])]
    #[TestWith([[0, 550_000], ['integer' => 0, 'fraction' => 550_000, 'value' => '0.550000']])]
    #[TestWith([[0, 1], ['integer' => 0, 'fraction' => 1, 'value' => '0.000001']])]
    #[TestWith([[-1, 550_000], ['integer' => -1, 'fraction' => 550_000, 'value' => '-0.450000']])]
    #[TestWith([[-2, 550_000], ['integer' => -2, 'fraction' => 550_000, 'value' => '-1.450000']])]
    #[TestWith([[-2, 1_000_050], ['integer' => -1, 'fraction' => 50, 'value' => '-0.999950']])]
    #[TestWith([[-2, -550_000], ['integer' => -3, 'fraction' => 450_000, 'value' => '-2.550000']])]
    #[TestWith([[-2, -1_550_000], ['integer' => -4, 'fraction' => 450_000, 'value' => '-3.550000']])]
    #[TestWith([[0, 1], ['integer' => 0, 'fraction' => 1, 'value' => '0.000001']])]
    #[TestWith([[0, 550_000], ['integer' => 0, 'fraction' => 550_000, 'value' => '0.550000']])]
    #[TestWith([[0, 1_550_000], ['integer' => 1, 'fraction' => 550_000, 'value' => '1.550000']])]
    #[TestWith([[0, -1], ['integer' => -1, 'fraction' => 999_999, 'value' => '-0.000001']])]
    #[TestWith([[0, -550_000], ['integer' => -1, 'fraction' => 450_000, 'value' => '-0.550000']])]
    #[TestWith([[0, -1_550_000], ['integer' => -2, 'fraction' => 450_000, 'value' => '-1.550000']])]
    public function testBasic(array $args, array $expected): void
    {
        $integer = $expected['integer'];
        $fraction = $expected['fraction'];
        $value = $expected['value'];
        $floatValue = (float)$expected['value'];

        $obj = Number::of(...$args);

        self::assertEquals($integer, $obj->integer());
        self::assertEquals($fraction, $obj->fraction());
        self::assertEquals($value, (string)$obj);
        self::assertEquals($floatValue, $obj->toFloat());
        self::assertEquals($floatValue === 0.0, $obj->isZero());
        self::assertEquals($floatValue > 0.0, $obj->isPositive());
        self::assertEquals($floatValue < 0.0, $obj->isNegative());
    }

    /**
     * @param array{integer: int, fraction: int, value: string} $expected
     */
    #[TestWith([1, ['integer' => 1, 'fraction' => 0, 'value' => '1.000000']])]
    #[TestWith([15, ['integer' => 15, 'fraction' => 0, 'value' => '15.000000']])]
    #[TestWith([-1, ['integer' => -1, 'fraction' => 0, 'value' => '-1.000000']])]
    #[TestWith([-15, ['integer' => -15, 'fraction' => 0, 'value' => '-15.000000']])]
    #[TestWith([0, ['integer' => 0, 'fraction' => 0, 'value' => '0.000000']])]
    #[TestWith(['1', ['integer' => 1, 'fraction' => 0, 'value' => '1.000000']])]
    #[TestWith(['1.01', ['integer' => 1, 'fraction' => 10_000, 'value' => '1.010000']])]
    #[TestWith(['1.55', ['integer' => 1, 'fraction' => 550_000, 'value' => '1.550000']])]
    #[TestWith(['15.550000', ['integer' => 15, 'fraction' => 550_000, 'value' => '15.550000']])]
    #[TestWith(['15.999999', ['integer' => 15, 'fraction' => 999_999, 'value' => '15.999999']])]
    #[TestWith(['15.999999999', ['integer' => 16, 'fraction' => 0, 'value' => '16.000000']])]
    #[TestWith(['1.0e-6', ['integer' => 0, 'fraction' => 1, 'value' => '0.000001']])]
    #[TestWith(['-1', ['integer' => -1, 'fraction' => 0, 'value' => '-1.000000']])]
    #[TestWith(['-1.01', ['integer' => -2, 'fraction' => 990_000, 'value' => '-1.010000']])]
    #[TestWith(['-1.550000', ['integer' => -2, 'fraction' => 450_000, 'value' => '-1.550000']])]
    #[TestWith(['-15.550000', ['integer' => -16, 'fraction' => 450_000, 'value' => '-15.550000']])]
    #[TestWith(['-15.999999', ['integer' => -16, 'fraction' => 1, 'value' => '-15.999999']])]
    #[TestWith(['-15.999999999', ['integer' => -16, 'fraction' => 0, 'value' => '-16.000000']])]
    #[TestWith(['0.55', ['integer' => 0, 'fraction' => 550_000, 'value' => '0.550000']])]
    #[TestWith(['0.00', ['integer' => 0, 'fraction' => 0, 'value' => '0.000000']])]
    #[TestWith(['-0.0', ['integer' => 0, 'fraction' => 0, 'value' => '0.000000']])]
    #[TestWith(['-0.55', ['integer' => -1, 'fraction' => 450_000, 'value' => '-0.550000']])]
    #[TestWith(['-1.0e-6', ['integer' => -1, 'fraction' => 999_999, 'value' => '-0.000001']])]
    #[TestWith([1.0, ['integer' => 1, 'fraction' => 0, 'value' => '1.000000']])]
    #[TestWith([1.2, ['integer' => 1, 'fraction' => 200_000, 'value' => '1.200000']])]
    #[TestWith([1.02, ['integer' => 1, 'fraction' => 20_000, 'value' => '1.020000']])]
    #[TestWith([1.999_999, ['integer' => 1, 'fraction' => 999_999, 'value' => '1.999999']])]
    #[TestWith([1.999_999_999, ['integer' => 2, 'fraction' => 0, 'value' => '2.000000']])]
    #[TestWith([5.999_999, ['integer' => 5, 'fraction' => 999_999, 'value' => '5.999999']])]
    #[TestWith([0.0, ['integer' => 0, 'fraction' => 0, 'value' => '0.000000']])]
    #[TestWith([0.2, ['integer' => 0, 'fraction' => 200_000, 'value' => '0.200000']])]
    #[TestWith([1.0e-6, ['integer' => 0, 'fraction' => 1, 'value' => '0.000001']])]
    #[TestWith([-0.1, ['integer' => -1, 'fraction' => 900_000, 'value' => '-0.100000']])]
    #[TestWith([-0.01, ['integer' => -1, 'fraction' => 990_000, 'value' => '-0.010000']])]
    #[TestWith([-0.55, ['integer' => -1, 'fraction' => 450_000, 'value' => '-0.550000']])]
    #[TestWith([-0.999_999, ['integer' => -1, 'fraction' => 1, 'value' => '-0.999999']])]
    #[TestWith([-0.999_999_999, ['integer' => -1, 'fraction' => 0, 'value' => '-1.000000']])]
    #[TestWith([-5.999_999, ['integer' => -6, 'fraction' => 1, 'value' => '-5.999999']])]
    #[TestWith([-1.0e-6, ['integer' => -1, 'fraction' => 999_999, 'value' => '-0.000001']])]
    #[Depends('testBasic')]
    public function testOfNumber(float|int|string $value, array $expected): void
    {
        $obj = Number::ofNumber($value);

        self::assertEquals($expected['integer'], $obj->integer());
        self::assertEquals($expected['fraction'], $obj->fraction());
        self::assertEquals($expected['value'], (string)$obj);
        self::assertEquals((float)$expected['value'], $obj->toFloat());
    }

    /**
     * @param array{int, int} $args
     * @param array{integer: int, fraction: int, value: string} $expected
     */
    #[TestWith([[5, 550_000], ['integer' => -6, 'fraction' => 450_000, 'value' => '-5.550000']])]
    #[TestWith([[-5, -550_000], ['integer' => 5, 'fraction' => 550_000, 'value' => '5.550000']])]
    #[TestWith([[-6, 450_000], ['integer' => 5, 'fraction' => 550_000, 'value' => '5.550000']])]
    #[TestWith([[1, 550_000], ['integer' => -2, 'fraction' => 450_000, 'value' => '-1.550000']])]
    #[TestWith([[-2, 550_000], ['integer' => 1, 'fraction' => 450_000, 'value' => '1.450000']])]
    #[TestWith([[-1, -450_000], ['integer' => 1, 'fraction' => 450_000, 'value' => '1.450000']])]
    #[TestWith([[0, 550_000], ['integer' => -1, 'fraction' => 450_000, 'value' => '-0.550000']])]
    #[TestWith([[0, -550_000], ['integer' => 0, 'fraction' => 550_000, 'value' => '0.550000']])]
    #[TestWith([[0, -450_000], ['integer' => 0, 'fraction' => 450_000, 'value' => '0.450000']])]
    #[TestWith([[-1, 550_000], ['integer' => 0, 'fraction' => 450_000, 'value' => '0.450000']])]
    #[Depends('testBasic')]
    public function testNegate(array $args, array $expected): void
    {
        $obj = Number::of(...$args)->negate();

        self::assertEquals($expected['integer'], $obj->integer());
        self::assertEquals($expected['fraction'], $obj->fraction());
        self::assertEquals($expected['value'], (string)$obj);
        self::assertEquals((float)$expected['value'], $obj->toFloat());
    }

    /**
     * @param array{int, int} $args
     * @param array{integer: int, fraction: int, value: string} $expected
     */
    #[TestWith([[5, 550_000], ['integer' => 5, 'fraction' => 550_000, 'value' => '5.550000']])]
    #[TestWith([[-5, -550_000], ['integer' => 5, 'fraction' => 550_000, 'value' => '5.550000']])]
    #[TestWith([[-6, 450_000], ['integer' => 5, 'fraction' => 550_000, 'value' => '5.550000']])]
    #[TestWith([[1, 550_000], ['integer' => 1, 'fraction' => 550_000, 'value' => '1.550000']])]
    #[TestWith([[-2, 550_000], ['integer' => 1, 'fraction' => 450_000, 'value' => '1.450000']])]
    #[TestWith([[-1, -450_000], ['integer' => 1, 'fraction' => 450_000, 'value' => '1.450000']])]
    #[TestWith([[0, 550_000], ['integer' => 0, 'fraction' => 550_000, 'value' => '0.550000']])]
    #[TestWith([[0, -550_000], ['integer' => 0, 'fraction' => 550_000, 'value' => '0.550000']])]
    #[TestWith([[0, -450_000], ['integer' => 0, 'fraction' => 450_000, 'value' => '0.450000']])]
    #[TestWith([[-1, 550_000], ['integer' => 0, 'fraction' => 450_000, 'value' => '0.450000']])]
    #[Depends('testNegate')]
    public function testAbs(array $args, array $expected): void
    {
        $obj = Number::of(...$args)->abs();

        self::assertEquals($expected['integer'], $obj->integer());
        self::assertEquals($expected['fraction'], $obj->fraction());
        self::assertEquals($expected['value'], (string)$obj);
        self::assertEquals((float)$expected['value'], $obj->toFloat());
    }

    /**
     * @param array{int, int} $number
     * @param array<array{int, int}> $items
     * @param array{int, int} $expected
     */
    #[TestWith([
        [1, 550_000],
        [[0, 450_000], [1, 1]],
        [3, 1],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, 550_000], [1, 1]],
        [3, 100_001],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, 550_000], [10, 550_000]],
        [12, 650_000],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, 550_000], [10, -550_000]],
        [11, 550_000],
    ])]
    #[TestWith([
        [0, 450_000],
        [[0, 550_000]],
        [1, 0],
    ])]
    #[TestWith([
        [-1, 550_000],
        [[0, 400_000]],
        [-1, 950_000],
    ])]
    #[TestWith([
        [-1, 550_000],
        [[0, 250_000], [0, 250_000]],
        [0, 50_000],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, -250_000], [0, -250_000]],
        [1, 50_000],
    ])]
    #[TestWith([
        [1, 550_000],
        [[-2, 450_000]],
        [0, 0],
    ])]
    #[TestWith([
        [1, 550_000],
        [[-1, -550_000]],
        [0, 0],
    ])]
    #[TestWith([
        [0, 0],
        [[0, -550_000]],
        [-1, 450_000],
    ])]
    #[TestWith([
        [10, 0],
        [[0, -550_000]],
        [9, 450_000],
    ])]
    #[TestWith([
        [10, 0],
        [[0, 550_000]],
        [10, 550_000],
    ])]
    #[Depends('testBasic')]
    public function testAdd(array $number, array $items, array $expected): void
    {
        $expected = Number::of(...$expected);
        $number = Number::of(...$number);
        $items = \array_map(static fn(array $args): Number => Number::of(...$args), $items);

        $actual = $number->add(...$items);

        self::assertEquals($expected->integer(), $actual->integer());
        self::assertEquals($expected->fraction(), $actual->fraction());
        self::assertEquals($expected->isPositive(), $actual->isPositive());
        self::assertEquals($expected->isZero(), $actual->isZero());
        self::assertEquals($expected->isNegative(), $actual->isNegative());
        self::assertEquals((string)$expected, (string)$actual);
        self::assertEquals($expected->toFloat(), $actual->toFloat());
    }

    /**
     * @param array{int, int} $number
     * @param array<array{int, int}> $items
     * @param array{int, int} $expected
     */
    #[TestWith([
        [1, 550_000],
        [[0, 500_000], [1, 1]],
        [0, 49_999],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, 550_000], [1, 1]],
        [-1, 999_999],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, 550_000], [10, 550_000]],
        [-10, 450_000],
    ])]
    #[TestWith([
        [1, 550_000],
        [[-2, 550_000]],
        [3, 0],
    ])]
    #[TestWith([
        [0, 450_000],
        [[0, 550_000]],
        [-1, 900_000],
    ])]
    #[TestWith([
        [-1, 550_000],
        [[0, 400_000]],
        [-1, 150_000],
    ])]
    #[TestWith([
        [-1, 550_000],
        [[0, 250_000], [0, 250_000]],
        [-1, 50_000],
    ])]
    #[TestWith([
        [1, 550_000],
        [[0, -250_000], [0, -250_000]],
        [2, 50_000],
    ])]
    #[TestWith([
        [1, 550_000],
        [[1, 450_000], [0, 100_000]],
        [0, 0],
    ])]
    #[TestWith([
        [-2, 450_000],
        [[-2, 450_000]],
        [0, 0],
    ])]
    #[TestWith([
        [0, 0],
        [[0, 550_000]],
        [-1, 450_000],
    ])]
    #[TestWith([
        [0, 450_000],
        [[0, 550_000]],
        [-1, 900_000],
    ])]
    #[TestWith([
        [10, 0],
        [[0, 550_000]],
        [9, 450_000],
    ])]
    #[Depends('testBasic')]
    public function testSub(array $number, array $items, array $expected): void
    {
        $expected = Number::of(...$expected);
        $number = Number::of(...$number);
        $items = \array_map(static fn(array $args): Number => Number::of(...$args), $items);

        $actual = $number->sub(...$items);

        self::assertEquals($expected->integer(), $actual->integer());
        self::assertEquals($expected->fraction(), $actual->fraction());
        self::assertEquals($expected->isPositive(), $actual->isPositive());
        self::assertEquals($expected->isZero(), $actual->isZero());
        self::assertEquals($expected->isNegative(), $actual->isNegative());
        self::assertEquals((string)$expected, (string)$actual);
        self::assertEquals($expected->toFloat(), $actual->toFloat());
    }
}
