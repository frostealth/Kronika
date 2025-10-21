<?php

declare(strict_types=1);

namespace Kronika\Utils\Math;

/**
 * @internal
 */
interface Math
{
    public function add(int $integer, int $fraction = 0): self;

    public function sub(int $integer, int $fraction = 0): self;

    public function integer(): int;

    /** @return non-negative-int */
    public function fraction(): int;

    /** @return array{int, non-negative-int} */
    public function parts(): array;
}
