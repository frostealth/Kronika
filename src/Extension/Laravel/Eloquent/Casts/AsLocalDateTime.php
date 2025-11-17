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

use Kronika\LocalDateTime;

/**
 * @extends CastsFormattableUnit<LocalDateTime>
 */
final readonly class AsLocalDateTime extends CastsFormattableUnit
{
    /** @param non-empty-string $format */
    public function __construct(
        string $format = 'Y-m-d\TH:i:s.u',
    ) {
        parent::__construct($format);
    }

    #[\Override]
    protected static function factory(): callable
    {
        return static function (string $format, string $value): LocalDateTime {
            try {
                return LocalDateTime::ofFormat($format, $value);
            } catch (\Throwable) {
                return LocalDateTime::parse($value);
            }
        };
    }
}
