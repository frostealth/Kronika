<?php

declare(strict_types=1);

namespace Kronika;

/**
 * @psalm-type Result=int<-1,1>
 */
final readonly class Comparison
{
    /**
     * @template T
     *
     * @psalm-param T $first
     * @psalm-param T $second
     */
    public static function compare(mixed $first, mixed $second): self
    {
        return new self($first <=> $second);
    }

    /**
     * @psalm-param Result $result
     */
    public function __construct(
        private int $result,
    ) {
        \assert(-1 <= $result && 1 >= $result);
    }

    public function equal(): bool
    {
        return 0 === $this->result;
    }

    public function notEqual(): bool
    {
        return ! $this->equal();
    }

    public function greater(): bool
    {
        return 1 === $this->result;
    }

    public function greaterOrEqual(): bool
    {
        return $this->greater() || $this->equal();
    }

    public function less(): bool
    {
        return -1 === $this->result;
    }

    public function lessOrEqual(): bool
    {
        return $this->less() || $this->equal();
    }

    /**
     * @psalm-return Result
     */
    public function value(): int
    {
        return $this->result;
    }
}
