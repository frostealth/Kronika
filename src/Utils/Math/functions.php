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

namespace Kronika\Utils\Math;


if (! \function_exists('\Kronika\Utils\Math\double_split')) {
    /**
     * Splits a number into integer and fraction parts.
     *
     * @param numeric $number
     * @param int<1, 15> $precision
     *
     * @return array{int, non-negative-int}
     *
     * @internal
     */
    function double_split(float|int|string $number, int $precision = 6): array
    {
        if (\is_int($number)) {
            return [$number, 0];
        }

        $number = \sprintf("%0{$precision}F", $number);
        \sscanf($number, "%d.%0{$precision}u", $integer, $fraction);

        return [$integer, $fraction ?? 0];
    }
}

if (! \function_exists('\\Kronika\\Utils\\Math\\double')) {
    /**
     * @param array{int, non-negative-int}|numeric $number
     * @param int<1, 15> $precision
     *
     * @internal
     */
    function double(array|float|int|string $number, int $precision = 6): float
    {
        if (\is_float($number)) {
            return $number;
        }
        if (\is_array($number)) {
            $number = \sprintf("%d.%0{$precision}u", $number[0], $number[1]);
        }

        return \current(\sscanf((string)$number, "%f0{$precision}"));
    }
}
