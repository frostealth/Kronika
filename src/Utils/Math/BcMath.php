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

namespace Kronika\Utils\Math;

use Kronika\Utils\Math;

/**
 * @psalm-import-type TFraction from Math
 * @psalm-import-type TPrecision from Math
 *
 * @psalm-internal Kronika\Utils
 * @internal
 */
final class BcMath implements Math
{
    /**
     * @param TFraction $fraction
     * @param TPrecision $precision
     */
    public static function of(int $integer, int $fraction, int $precision): self
    {
        return new self(self::prepare($integer, $fraction, $precision), $precision);
    }

    /**
     * @param numeric-string $number
     * @param TPrecision $precision
     */
    private function __construct(
        private string $number,
        private readonly int $precision,
    ) {
    }

    #[\Override]
    public function add(int $integer, int $fraction = 0): self
    {
        $this->number = \bcadd($this->number, self::prepare($integer, $fraction, $this->precision), $this->precision);

        return $this;
    }

    #[\Override]
    public function sub(int $integer, int $fraction = 0): self
    {
        $this->number = \bcsub($this->number, self::prepare($integer, $fraction, $this->precision), $this->precision);

        return $this;
    }

    #[\Override]
    public function integer(): int
    {
        return $this->parts()[0];
    }

    #[\Override]
    public function fraction(): int
    {
        return $this->parts()[1];
    }

    #[\Override]
    public function parts(): array
    {
        return \sscanf($this->number, "%d.%{$this->precision}d");
    }

    /**
     * @param TFraction $fraction
     * @param TPrecision $precision
     *
     * @return numeric-string
     */
    private static function prepare(int $integer, int $fraction, int $precision): string
    {
        return \sprintf("%d.%0{$precision}d", $integer, $fraction);
    }
}
