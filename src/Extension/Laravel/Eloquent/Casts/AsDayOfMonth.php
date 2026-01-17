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

namespace Kronika\Extension\Laravel\Eloquent\Casts;

use Kronika\Date\DayOfMonth;

/**
 * @extends CastsSimpleUnit<DayOfMonth, int<1, 12>>
 */
final readonly class AsDayOfMonth extends CastsSimpleUnit
{
    #[\Override]
    protected static function factory(): callable
    {
        return DayOfMonth::of(...);
    }
}
