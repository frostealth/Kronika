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
 * @psalm-internal Kronika\Utils
 * @internal
 */
final class References
{
    /** @var array<string, \WeakReference> */
    private array $references = [];
    private \WeakMap $map;

    public function __construct()
    {
        $this->map = new \WeakMap();
    }

    /**
     * @template TType of object
     * @template TArg
     *
     * @param callable(TArg...): TType $factory
     * @param TArg ...$args
     *
     * @return TType
     *
     * @psalm-suppress InvalidReturnType
     */
    public function ref(callable $factory, mixed ...$args): object
    {
        $key = self::key(...$args);

        $instance = ($this->references[$key] ?? null)?->get();
        if (\is_object($instance)) {
            /** @psalm-suppress InvalidReturnStatement */
            return $instance;
        }

        $instance = $factory(...$args);
        $this->references[$key] = \WeakReference::create($instance);
        /** @psalm-suppress InvalidArrayAssignment */
        $this->mapFor($instance)['key'] = $key;

        return $instance;
    }

    /**
     * @param non-empty-string $key
     * @param (callable(mixed...): mixed)|mixed $held
     */
    public function map(object $holder, string $key, mixed $held, mixed ...$args): mixed
    {
        /** @psalm-suppress UnsupportedReferenceUsage */
        $values = &$this->mapFor($holder)['values'];
        if (! \array_key_exists($key, $values)) {
            $value = \is_callable($held) ? $held(...$args) : $held;
            $values[$key] = $value === $holder ? \WeakReference::create($holder) : $value;
        }

        $value = $values[$key];

        return $value instanceof \WeakReference ? $value->get() : $value;
    }

    public function onDestruction(object $instance): void
    {
        $key = $this->map[$instance]['key'] ?? null;
        /** @psalm-suppress PossiblyNullArrayOffset */
        unset($this->references[$key], $this->map[$instance]);
    }

    public function reset(): void
    {
        $this->references = [];
        $this->map = new \WeakMap();
    }

    private static function &key(mixed ...$args): string
    {
        $key = '';
        \ksort($args);
        foreach ($args as $i => $value) {
            $key .= \sprintf('{%s=%s}', $i, match (true) {
                \is_scalar($value),
                    $value instanceof \Stringable => (string)$value,
                $value instanceof \DateTimeZone => $value->getName(),
                $value instanceof \BackedEnum => (string)$value->value,
                $value instanceof \UnitEnum => $value->name,
                default => \var_export($value, true),
            });
        }

        return $key;
    }

    private function &mapFor(object $holder): array
    {
        $this->map[$holder] ??= ['values' => []];

        /** @psalm-suppress NonVariableReferenceReturn */
        return $this->map[$holder];
    }
}
