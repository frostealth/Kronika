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

namespace Kronika\Utils;

/**
 * @internal
 */
trait RescueTrait
{
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $method = \substr($name, offset: 3);
        if (! \str_starts_with($name, 'try') || ! \method_exists(static::class, $method)) {
            throw new \Error(\sprintf('Call to undefined method %s::%s()', static::class, $name));
        }

        try {
            return static::$method(...$arguments);
        } catch (\Throwable) {
            return null;
        }
    }
}
