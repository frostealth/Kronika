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

use Kronika\Time;

/**
 * @extends CastsFormattableUnit<Time>
 */
final readonly class AsTime extends CastsFormattableUnit
{
    /** @param non-empty-string $format */
    public function __construct(
        string $format = 'H:i:s.u',
    ) {
        parent::__construct($format);
    }

    #[\Override]
    protected static function factory(): callable
    {
        return static fn(string $format, string $value): Time => Time::tryOfFormat(
            format: $format,
            time: $value,
        ) ?? Time::parse($value);
    }
}
