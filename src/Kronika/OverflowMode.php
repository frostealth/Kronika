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

namespace Kronika;

enum OverflowMode
{
    /** Roll forward when overflowing. */
    case Roll;

    /** Set the last available value when overflowing. */
    case Clamp;

    /** Throw an exception when overflowing. */
    case Strict;
}
