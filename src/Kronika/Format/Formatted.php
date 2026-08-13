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

namespace Kronika\Format;

abstract readonly class Formatted
{
    /**
     * @param non-empty-string $format
     */
    public function __construct(
        private string $format,
        private string $value,
    ) {
    }

    /** @return non-empty-string */
    final public function format(): string
    {
        return $this->format;
    }

    final public function value(): string
    {
        return $this->value;
    }
}
