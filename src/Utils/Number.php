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
 * @internal
 */
final readonly class Number
{
    private const int DENOMINATOR = 10 ** 6;

    public static function of(int $integer, int $fraction): self
    {
        $integer += \intdiv($fraction, self::DENOMINATOR);
        $fraction = $fraction % self::DENOMINATOR;
        if ($fraction < 0) {
            $integer--;
            $fraction += self::DENOMINATOR;
        }

        return new self($integer, $fraction);
    }

    /**
     * @param numeric $number
     *
     * @no-named-arguments
     */
    public static function ofNumber(float|int|string $number): self
    {
        if (\is_int($number)) {
            return self::of($number, fraction: 0);
        }

        $number = \sprintf('%.6F', $number);
        [$integer, $fraction] = \sscanf($number, '%d.%6d');
        if (\str_starts_with($number, '-')) {
            $fraction = -$fraction;
        }

        return self::of($integer, $fraction);
    }

    /** @param non-negative-int $fraction */
    public function __construct(
        private int $integer,
        private int $fraction,
    ) {
    }

    public function integer(): int
    {
        return $this->integer;
    }

    /** @return non-negative-int */
    public function fraction(): int
    {
        return $this->fraction;
    }

    /** @no-named-arguments */
    public function add(self ...$others): self
    {
        $integer = $this->integer;
        $fraction = $this->fraction;
        foreach ($others as $other) {
            $integer += $other->integer;
            $fraction += $other->fraction;
        }

        return self::of($integer, $fraction);
    }

    /** @no-named-arguments */
    public function sub(self ...$others): self
    {
        $integer = $this->integer;
        $fraction = $this->fraction;
        foreach ($others as $other) {
            $integer -= $other->integer;
            $fraction -= $other->fraction;
        }

        return self::of($integer, $fraction);
    }

    public function isPositive(): bool
    {
        return ! $this->isNegative() && ! $this->isZero();
    }

    public function isZero(): bool
    {
        return $this->integer === 0 && $this->fraction === 0;
    }

    public function isNegative(): bool
    {
        return $this->integer < 0;
    }

    public function abs(): self
    {
        return $this->isNegative() ? $this->negate() : $this;
    }

    public function negate(): self
    {
        return self::of(-$this->integer, -$this->fraction);
    }

    public function toFloat(): float
    {
        return (float)(string)$this;
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        $sign = $this->isNegative() ? '-' : '';
        $integer = $this->integer;
        $fraction = $this->fraction;
        if ($this->isNegative() && $fraction !== 0) {
            $integer++;
            $fraction = self::DENOMINATOR - $fraction;
        }

        return \sprintf("%s%d.%06d", $sign, \abs($integer), $fraction);
    }
}
