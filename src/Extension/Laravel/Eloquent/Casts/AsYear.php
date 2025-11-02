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

use Kronika\Date\Year;

/**
 * @extends CastsSimpleUnit<Year, int>
 */
final readonly class AsYear extends CastsSimpleUnit
{
    #[\Override]
    protected static function factory(): callable
    {
        return Year::of(...);
    }
}
