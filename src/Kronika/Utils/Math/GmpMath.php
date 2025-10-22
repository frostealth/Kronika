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

/**
 * @psalm-import-type TFraction from Math
 * @psalm-import-type TPrecision from Math
 *
 * @psalm-internal Kronika\Utils
 * @internal
 */
final class GmpMath implements Math
{
    /**
     * @param TFraction $fraction
     * @param TPrecision $precision
     */
    public static function of(int $integer, int $fraction, int $precision): self
    {
        return new self(self::prepare($integer, $fraction, $precision), $precision);
    }

    private readonly \GMP $delimiter;

    /** @param TPrecision $precision */
    private function __construct(
        private \GMP $number,
        private readonly int $precision,
    ) {
        $this->delimiter = new \GMP((string) (10 ** $precision));
    }

    #[\Override]
    public function add(int $integer, int $fraction = 0): self
    {
        $this->number = \gmp_add($this->number, self::prepare($integer, $fraction, $this->precision));

        return $this;
    }

    #[\Override]
    public function sub(int $integer, int $fraction = 0): self
    {
        $this->number = \gmp_sub($this->number, self::prepare($integer, $fraction, $this->precision));

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
        return \array_map(
            static fn(\GMP $part): int => \gmp_intval($part),
            \gmp_div_qr($this->number, $this->delimiter),
        );
    }

    /**
     * @param TFraction $fraction
     * @param TPrecision $precision
     */
    private static function prepare(int $integer, int $fraction, int $precision): \GMP
    {
        return new \GMP(\sprintf("%d%0{$precision}d", $integer, $fraction));
    }
}
