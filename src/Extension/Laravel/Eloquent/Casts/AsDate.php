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

use Kronika\Date;

/**
 * @extends CastsFormattableUnit<Date>
 */
final readonly class AsDate extends CastsFormattableUnit
{
    /** @param non-empty-string $format */
    public function __construct(
        string $format = 'Y-m-d',
    ) {
        parent::__construct($format);
    }

    #[\Override]
    protected static function factory(): callable
    {
        return Date::ofFormat(...);
    }
}
