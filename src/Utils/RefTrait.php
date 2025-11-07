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
 *
 * @internal
 */
trait RefTrait
{
    /**
     * @template TArg
     *
     * @param (callable(TArg...): TType)|null $factory
     * @param TArg ...$args
     *
     * @return TType
     */
    final protected static function ref(?callable $factory = null, mixed ...$args): static
    {
        $factory ??= static fn (mixed ...$args): object => new static(...$args);

        return references()->get(static::class, $factory, ...$args);
    }

    /**
     * @template MType of mixed
     * @template MArg
     *
     * @param MType|(callable(MArg...): MType) $held
     * @param non-empty-string $key
     * @param MArg ...$args
     *
     * @return MType
     */
    final protected static function map(object $holder, object|callable $held, string $key, mixed ...$args): mixed
    {
        return references()->mapped(static::class . ':' . $key, $holder, $held, $holder, ...$args);
    }

    /**
     * @template MType of mixed
     * @template MArg
     *
     * @param MType|(callable(MArg...): MType) $held
     * @param non-empty-string $key
     * @param MArg ...$args
     *
     * @return MType
     */
    final protected function remember(object|callable $held, string $key, mixed ...$args): mixed
    {
        return self::map($this, $held, $key, ...$args);
    }

    /** @internal */
    public function __destruct()
    {
        references()->cleanUp();
    }
}