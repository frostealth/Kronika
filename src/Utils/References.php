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
    /** @var array<class-string, array<non-empty-string, \WeakReference>> */
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
     * @param class-string<TType> $type
     * @param callable(TArg...): TType $factory
     * @param TArg ...$args
     *
     * @return TType
     */
    public function ref(string $type, callable $factory, mixed ...$args): object
    {
        $this->references[$type] ??= [];
        $key = self::key(...$args);

        $instance = ($this->references[$type][$key] ?? null)?->get();
        if (\is_a($instance, $type, allow_string: true)) {
            return $instance;
        }

        $instance = $factory(...$args);
        $this->references[$type][$key] = \WeakReference::create($instance);

        return $instance;
    }

    /**
     * @template TType of mixed
     * @template TArg
     *
     * @param non-empty-string $key
     * @param TType|(callable(TArg...): TType) $held
     * @param TArg ...$args
     *
     * @return TType
     */
    public function map(object $holder, string $key, mixed $held, mixed ...$args): mixed
    {
        $this->map[$holder] ??= [];

        return $this->map[$holder][$key] ??= \is_callable($held) ? $held(...$args) : $held;
    }

    public function remove(object $instance): void
    {
        unset($this->map[$instance]);
        if (! isset($this->references[$instance::class])) {
            return;
        }

        foreach ($this->references[$instance::class] ?? [] as $key => $reference) {
            if ($reference->get() === $instance) {
                unset($this->references[$instance::class][$key]);
            }
        }
        if ($this->references[$instance::class] === []) {
            unset($this->references[$instance::class]);
        }
    }

    public function cleanUp(): void
    {
        foreach ($this->references as $type => $references) {
            foreach ($references as $key => $reference) {
                if ($reference->get() === null) {
                    unset($this->references[$type][$key]);
                }
            }
            if ($this->references[$type] === []) {
                unset($this->references[$type]);
            }
        }
    }

    public function reset(): void
    {
        $this->references = [];
        $this->map = new \WeakMap();
    }

    private static function key(mixed ...$args): string
    {
        static $hash = static function (int|string $key, mixed $value): string {
            $prepareValue = static function (mixed $value): string {
                if (\is_string($value)) {
                    return $value;
                }
                if (\is_numeric($value)) {
                    return (string)$value;
                }
                if (\is_null($value)) {
                    return 'null';
                }
                if (\is_bool($value)) {
                    return $value ? 'true' : 'false';
                }
                if (! \is_object($value)) {
                    return \var_export($value, true);
                }

                if ($value instanceof \Stringable) {
                    return (string)$value;
                }
                if ($value instanceof \BackedEnum) {
                    return (string)$value->value;
                }
                if ($value instanceof \UnitEnum) {
                    return $value->name;
                }
                if (\method_exists($value, 'value')) {
                    return (string)$value->value();
                }
                if (\method_exists($value, 'number')) {
                    return (string)$value->number();
                }

                return \var_export($value, return: true);
            };

            return \sprintf('[%s:%s]', $key, $prepareValue($value));
        };

        \ksort($args);

        return \implode('||', \array_map($hash, \array_keys($args), \array_values($args)));
    }
}
