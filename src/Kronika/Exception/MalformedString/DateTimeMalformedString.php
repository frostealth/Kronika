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

namespace Kronika\Exception\MalformedString;

use Kronika\Exception\MalformedString;

final class DateTimeMalformedString extends \DateMalformedStringException implements MalformedString
{
    /** @internal */
    public static function wrap(\Throwable $original): self
    {
        if ($original instanceof self) {
            return $original;
        }

        return new self(
            message: $original->getMessage(),
            code: $original->getCode(),
            previous: $original,
        );
    }
}
