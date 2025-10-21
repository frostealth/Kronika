<?php

declare(strict_types=1);

namespace Kronika\Utils\Math;

/**
 * @psalm-internal Kronika\Utils
 * @internal
 */
final class BcMath implements Math
{
    public static function of(int $integer, int $fraction, int $precision): self
    {
        return new self(self::prepare($integer, $fraction, $precision), $precision);
    }

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
        return \sscanf($this->number, '%d.%6d');
    }

    /**
     * @param int $integer
     * @param int $fraction
     * @param int $precision
     *
     * @return string
     */
    private static function prepare(int $integer, int $fraction, int $precision): string
    {
        return \sprintf("%d.%0{$precision}d", $integer, $fraction);
    }
}
