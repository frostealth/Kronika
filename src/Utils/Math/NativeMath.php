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
 * @psalm-type TDelimiter=non-negative-int
 *
 * @psalm-internal Kronika\Utils
 * @internal
 */
final class NativeMath implements Math
{
    /**
     * @param TPrecision $precision
     */
    public static function of(int $integer, int $fraction, int $precision): self
    {
        $delimiter = 10 ** $precision;
        [$integer, $fraction] = self::normalize($integer, $fraction, $delimiter);

        return new self($integer, $fraction, $delimiter);
    }

    /**
     * @param TFraction $fraction
     * @param TDelimiter $delimiter
     */
    private function __construct(
        private int $integer,
        private int $fraction,
        private readonly int $delimiter,
    ) {
    }

    #[\Override]
    public function add(int $integer, int $fraction = 0): self
    {
        if ($fraction === 0 && $this->fraction === 0) {
            $this->integer += $integer;

            return $this;
        }

        $delimiter = $this->delimiter;
        [$integer, $fraction] = self::prepare($integer, $fraction, $delimiter);
        [$this->integer, $this->fraction] = self::prepare($this->integer, $this->fraction, $delimiter);

        $integer += $this->integer;
        $fraction += $this->fraction;

        [$this->integer, $this->fraction] = self::normalize($integer, $fraction, $delimiter);

        return $this;
    }

    #[\Override]
    public function sub(int $integer, int $fraction = 0): self
    {
        return $this->add(-$integer, $fraction);
    }

    #[\Override]
    public function integer(): int
    {
        return $this->integer;
    }

    #[\Override]
    public function fraction(): int
    {
        return \abs($this->fraction);
    }

    #[\Override]
    public function parts(): array
    {
        return [$this->integer(), $this->fraction()];
    }

    /**
     * @param TDelimiter $delimiter
     *
     * @return array{int, TFraction}
     */
    private static function prepare(int $integer, int $fraction, int $delimiter): array
    {
        if ($fraction === 0) {
            return [$integer, $fraction];
        }
        if ($fraction < 0) {
            $fraction = \abs($fraction);
        }
        if ($fraction >= $delimiter) {
            [$integer, $fraction] = self::normalize($integer, $fraction, $delimiter);
        }
        $fraction = $integer < 0 ? -$fraction : $fraction;

        return [$integer, $fraction];
    }

    /**
     * @param non-negative-int $delimiter
     *
     * @return array{int, non-negative-int}
     */
    private static function normalize(int $integer, int $fraction, int $delimiter): array
    {
        if ($fraction === 0) {
            return [$integer, $fraction];
        }

        $integer -= $integer < 0 ? -1 : 1;
        $fraction += $integer < 0 ? -$delimiter : $delimiter;

        $integer += (int) ($fraction / $delimiter);
        $fraction = \abs($fraction % $delimiter);

        return [$integer, $fraction];
    }
}
