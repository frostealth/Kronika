<?php

declare(strict_types=1);

namespace Kronika\Utils;

use Kronika\Utils\Math\BcMath;
use Kronika\Utils\Math\GmpMath;
use Kronika\Utils\Math\Math;
use Kronika\Utils\Math\NativeMath;

if (! \function_exists('math')) {
    /**
     * @psalm-param int<1,1000000000> $precision
     * @psalm-param 'auto'|'bcmath'|'gmp'|'native' $mode
     *
     * @internal
     */
    function math(int $integer, int $fraction, int $precision, string $mode = 'auto'): Math
    {
        if ($mode === 'auto') {
            $mode = match (true) {
                \extension_loaded('bcmath') => 'bcmath',
                \extension_loaded('gmp') => 'gmp',
                default => 'native',
            };
        }

        return match ($mode) {
            'bcmath' => BcMath::of($integer, $fraction, $precision),
            'gmp' => GmpMath::of($integer, $fraction, $precision),
            'native' => NativeMath::of($integer, $fraction, $precision),
        };
    }
}

if (! \function_exists('weak')) {
    /**
     * @template TType of object
     * @template TArgs
     *
     * @param class-string<TType> $type
     * @param callable(TArgs...):TType $factory
     * @param TArgs ...$args
     *
     * @return TType
     *
     * @internal
     */
    function weak(string $type, callable $factory, mixed ...$args): object
    {
        return weak_registry()->get($type, $factory, ...$args);
    }
}

if (! \function_exists('weak_clean_up')) {
    /**
     * @internal
     */
    function weak_clean_up(): void
    {
        weak_registry()->cleanUp();
    }
}

if (! \function_exists('weak_registry')) {
    /**
     * @psalm-internal Kronika\Utils
     * @internal
     */
    function weak_registry(): WeakRegistry
    {
        static $registry = new WeakRegistry();

        return $registry;
    }
}
