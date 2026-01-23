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
     * @param numeric $decimal
     *
     * @no-named-arguments
     */
    public static function ofDecimal(float|int|string $decimal): self
    {
        if (\is_int($decimal)) {
            return self::of($decimal, fraction: 0);
        }

        $decimal = \sprintf('%.6F', $decimal);
        \sscanf($decimal, '%d.%6d', $integer, $fraction);
        if (\str_starts_with($decimal, '-')) {
            $fraction = -$fraction;
        }

        /** @psalm-suppress PossiblyInvalidArgument */
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
        return (float)$this->toDecimal();
    }

    /**
     * @return non-empty-string
     *
     * @psalm-suppress MoreSpecificReturnType
     */
    public function toDecimal(): string
    {
        $sign = $this->isNegative() ? '-' : '';
        $integer = $this->integer;
        $fraction = $this->fraction;
        if ($this->isNegative() && $fraction !== 0) {
            $integer++;
            $fraction = self::DENOMINATOR - $fraction;
        }

        /** @psalm-suppress LessSpecificReturnStatement */
        return \sprintf("%s%d.%06d", $sign, \abs($integer), $fraction);
    }

    /** @return non-empty-string */
    #[\Override]
    public function __toString(): string
    {
        return $this->toDecimal();
    }
}
