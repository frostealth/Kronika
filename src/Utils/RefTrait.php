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
        $factory ??= static fn(mixed ...$args): object => new static(...$args);

        return references()->ref(static::class, $factory, ...$args);
    }

    /**
     * @template THolder of object
     * @template TArg
     *
     * @param THolder $holder
     * @param TType|(callable(THolder, TArg...): TType) $held
     * @param (callable(THolder, TArg...): bool)|null $when
     * @param non-empty-string|null $remember
     * @param TArg ...$args
     *
     * @return TType
     */
    final protected static function map(
        object $holder,
        self|callable $held,
        ?callable $when = null,
        ?string $remember = null,
        mixed ...$args,
    ): static {
        if ($when !== null && ! $when($holder, ...$args)) {
            return \is_callable($held) ? $held($holder, ...$args) : $held;
        }

        $instance = references()->map($holder, static::class, $held, $holder, ...$args);
        if ($remember !== null) {
            $instance->remember($holder, self::method($remember));
        }

        return $instance;
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private static function method(string $name): string
    {
        return static::class . '::' . $name;
    }

    /**
     * @template RType of mixed
     * @template RArg
     *
     * @param RType|(callable(TType, RArg...): RType) $held
     * @param non-empty-string $key
     * @param (callable(): bool)|null $when
     * @param RArg ...$args
     *
     * @return RType
     */
    final protected function remember(mixed $held, string $key, ?callable $when = null, mixed ...$args): mixed
    {
        if ($when !== null && ! $when()) {
            return \is_callable($held) ? $held($this, ...$args) : $held;
        }

        return references()->map($this, $key, $held, $this, ...$args);
    }

    /** @internal */
    public function __destruct()
    {
        references()->remove($this);
    }
}
