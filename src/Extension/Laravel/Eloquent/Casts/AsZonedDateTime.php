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

use Kronika\ZonedDateTime;

/**
 * @extends CastsFormattableUnit<ZonedDateTime>
 */
final readonly class AsZonedDateTime extends CastsFormattableUnit
{
    /** @param non-empty-string $format */
    public function __construct(
        string $format = 'Y-m-d\TH:i:s.uP',
    ) {
        parent::__construct($format);
    }

    #[\Override]
    protected static function factory(): callable
    {
        return static function (string $format, string $value): ZonedDateTime {
            try {
                return ZonedDateTime::ofFormat($format, $value);
            } catch (\Throwable) {
                return ZonedDateTime::parse($value);
            }
        };
    }
}
