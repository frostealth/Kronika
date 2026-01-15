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


use Kronika\Time\Second;

/**
 * @extends CastsSimpleUnit<Second, float>
 */
final readonly class AsSecond extends CastsSimpleUnit
{
    #[\Override]
    protected static function factory(): callable
    {
        return static fn (float $value): Second => Second::of(...\sscanf((string)$value, '%d.%6d'));
    }
}
