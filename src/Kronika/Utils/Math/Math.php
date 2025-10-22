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

/**
 * @psalm-type TFraction=non-negative-int
 * @psalm-type TPrecision=int<1,15>
 *
 * @internal
 */
interface Math
{
    /** @param TFraction $fraction */
    public function add(int $integer, int $fraction = 0): self;

    /** @param TFraction $fraction */
    public function sub(int $integer, int $fraction = 0): self;

    public function integer(): int;

    /** @return TFraction $fraction */
    public function fraction(): int;

    /** @return list<int, TFraction> */
    public function parts(): array;
}
