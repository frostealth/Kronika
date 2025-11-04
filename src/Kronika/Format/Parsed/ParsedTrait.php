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

namespace Kronika\Format\Parsed;

use Kronika\Format\Exception\FormatterError;

/**
 * @psalm-internal Kronika\Format
 * @internal
 */
trait ParsedTrait
{
    /**
     * @template TReturn of object
     * @template TValue
     *
     * @param non-empty-string $unit
     * @param callable(TValue...): TReturn $factory
     * @param (callable(): TReturn)|null $fallback
     * @param TValue|null ...$args
     *
     * @return TReturn
     *
     * @throws FormatterError
     */
    protected static function wrap(string $unit, callable $factory, ?callable $fallback, mixed ...$args): object
    {
        if (\array_any($args, static fn(mixed $arg): bool => \is_null($arg))) {
            return ($fallback ?? fn() => throw new FormatterError("Failed to parse $unit"))();
        }

        try {
            return $factory(...$args);
        } catch (\Throwable $e) {
            if ($e instanceof FormatterError) {
                throw $e;
            }

            throw new FormatterError("Failed to parse $unit", previous: $e);
        }
    }
}
