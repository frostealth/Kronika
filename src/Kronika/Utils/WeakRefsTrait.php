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
 * @template TType of static
 * @template TArgs
 * @internal
 */
trait WeakRefsTrait
{
    /**
     * @param ?callable(TArgs...):TType $factory
     * @param TArgs ...$args
     *
     * @return TType
     */
    final protected static function weak(?callable $factory = null, mixed ...$args): static
    {
        $factory ??= static fn (...$args): static => new static(...$args);

        return weak(static::class, $factory, ...$args);
    }

    /** @internal */
    public function __destruct()
    {
        weak_clean_up();
    }
}