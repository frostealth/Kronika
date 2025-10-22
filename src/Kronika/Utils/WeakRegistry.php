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
final class WeakRegistry
{
    /** @var array<non-empty-string, \WeakReference<object>> */
    private array $references = [];

    /**
     * @template TType of object
     * @template TArg
     *
     * @param class-string<TType> $type
     * @param callable(TArg...):TType $factory
     * @param TArg ...$args
     *
     * @return TType
     */
    public function get(string $type, callable $factory, mixed ...$args): object
    {
        $this->cleanUp();

        $key = $this->key($type, ...$args);
        $reference = $this->references[$key] ?? null;
        $instance = $reference?->get();
        if ($instance !== null) {
            return $instance;
        }

        $instance = $factory(...$args);
        $this->references[$key] = \WeakReference::create($instance);

        return $instance;
    }

    public function cleanUp(): void
    {
        $this->references = \array_filter(
            $this->references,
            static fn (\WeakReference $reference): bool => $reference->get() !== null,
        );
    }

    private function key(mixed ...$args): string
    {
        static $hash = static function (int|string $key, mixed $value): string {
            $prepareValue = static function (mixed $value): string {
                if (\is_string($value)) {
                    return $value;
                }
                if (\is_numeric($value)) {
                    return (string) $value;
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
                    return (string) $value;
                }
                if ($value instanceof \BackedEnum) {
                    return (string) $value->value;
                }
                if ($value instanceof \UnitEnum) {
                    return $value->name;
                }
                if (\method_exists($value, 'value')) {
                    return (string) $value->value();
                }
                if (\method_exists($value, 'number')) {
                    return (string) $value->number();
                }

                return \var_export($value, true);
            };

            return \sprintf('[%s:%s]', $key, $prepareValue($value));
        };

        \ksort($args);

        return \implode('||', \array_map($hash, \array_keys($args), \array_values($args)));
    }
}
