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
 * @psalm-type Result=int<-1,1>
 */
final readonly class Compared
{
    /**
     * @template T
     *
     * @param T $first
     * @param T $second
     *
     * @internal
     */
    public static function compare(mixed $first, mixed $second): self
    {
        return new self($first <=> $second);
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
