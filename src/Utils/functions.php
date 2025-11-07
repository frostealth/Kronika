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

namespace Kronika\Utils;

if (! \function_exists('\\Kronika\\Utils\\math')) {
    /**
     * @param non-negative-int $fraction
     * @param int<1,15> $precision
     *
     * @internal
     */
    function math(int $integer, int $fraction, int $precision): Math
    {
        if (extension_loaded('bcmath')) {
            return Math\BcMath::of($integer, $fraction, $precision);
        }
        if (extension_loaded('gmp')) {
            return Math\GmpMath::of($integer, $fraction, $precision);
        }

        return Math\NativeMath::of($integer, $fraction, $precision);
    }
}

if (! \function_exists('\\Kronika\\Utils\\references')) {
    /**
     * @psalm-internal Kronika\Utils
     * @internal
     */
    function references(): References
    {
        static $references = new References();

        return $references;
    }
}
