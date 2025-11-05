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

final class DateMalformedString extends \DateMalformedStringException implements MalformedString
{
    /** @internal */
    public static function wrap(\Throwable $origin): self
    {
        if ($origin instanceof self) {
            return $origin;
        }

        return new self($origin->getMessage(), $origin->getCode(), $origin);
    }
}
