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
 * Represents a comparison result.
 *
 * ```
 * $result = Compared::of($first <=> $second);
 * // check if $first is less than $second
 * $result->less();
 * // check if $first is less than or equal to $second
 * $result->lessOrEqual();
 * // check if $first is equal to $second
 * $result->equal();
 * // check if $first is not equal to $second
 * $result->notEqual();
 * // check if $first is greater than or equal to $second
 * $result->greaterOrEqual();
 * // check if $first is greater than $second
 * $result->greater();
 * ```
 *
 * @psalm-type TResult=int<-1,1>
 */
final readonly class Compared
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    /** @param TResult $result */
    public static function of(int $result): self
    {
        return new self($result);
    }

    /** @param TResult $result */
    private function __construct(
        private int $result,
    ) {
        self::assertValue($result);
    }

    public function less(): bool
    {
        return $this->result === self::LESS;
    }

    public function lessOrEqual(): bool
    {
        return $this->result !== self::GREATER;
    }

    public function equal(): bool
    {
        return $this->result === self::EQUAL;
    }

    public function notEqual(): bool
    {
        return $this->result !== self::EQUAL;
    }

    public function greaterOrEqual(): bool
    {
        return $this->result !== self::LESS;
    }

    public function greater(): bool
    {
        return $this->result === self::GREATER;
    }

    /** @return TResult */
    public function value(): int
    {
        return $this->result;
    }

    private static function assertValue(int $value): void
    {
        if ($value < self::LESS || $value > self::GREATER) {
            throw new \RuntimeException("Comparison result must be equal to -1, 0 or 1, got [$value]");
        }
    }
}
