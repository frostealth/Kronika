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
 * @psalm-type Result=int<-1,1>
 */
final readonly class Compared
{
    /** @param Result $result */
    public static function of(int $result): self
    {
        return new self($result);
    }

    /** @param Result $result */
    private function __construct(
        private int $result,
    ) {
        \assert(-1 <= $result && $result <= 1);
    }

    public function less(): bool
    {
        return -1 === $this->result;
    }

    public function lessOrEqual(): bool
    {
        return $this->less() || $this->equal();
    }

    public function equal(): bool
    {
        return 0 === $this->result;
    }

    public function notEqual(): bool
    {
        return ! $this->equal();
    }

    public function greaterOrEqual(): bool
    {
        return $this->greater() || $this->equal();
    }

    public function greater(): bool
    {
        return 1 === $this->result;
    }

    /** @return Result */
    public function value(): int
    {
        return $this->result;
    }
}
