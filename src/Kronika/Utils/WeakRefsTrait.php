<?php

declare(strict_types=1);

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