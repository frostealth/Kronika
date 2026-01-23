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
trait RefTrait
{
    /**
     * @template TArg
     *
     * @param (callable(TArg...): static)|null $factory
     * @param TArg ...$args
     */
    final protected static function ref(?callable $factory = null, mixed ...$args): static
    {
        $factory ??= static fn(mixed ...$args): object => new static(...$args);

        return self::references()->ref($factory, ...$args);
    }

    /**
     * @param mixed|(callable(mixed, mixed...): static) $held
     * @param (callable(mixed, mixed...): bool)|null $when
     * @param non-empty-string|null $remember
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

        $instance = self::references()->map($holder, static::class, $held, $holder, ...$args);
        if ($remember !== null) {
            $instance->remember($holder, $remember);
        }

        return $instance;
    }

    /**
     * @param non-empty-string $key
     * @param (callable(): bool)|null $when
     */
    final protected function remember(mixed $held, string $key, ?callable $when = null, mixed ...$args): mixed
    {
        if ($when !== null && ! $when()) {
            return \is_callable($held) ? $held($this, ...$args) : $held;
        }

        return self::references()->map($this, $key, $held, $this, ...$args);
    }

    private static function references(): References
    {
        /** @psalm-suppress InternalClass */
        static $references = new References();

        return $references;
    }

    /** @internal */
    public function __destruct()
    {
        self::references()->onDestruction($this);
    }
}
