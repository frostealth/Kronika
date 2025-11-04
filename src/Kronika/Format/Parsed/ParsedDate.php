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

namespace Kronika\Format\Parsed;

use Kronika\Date\DayOfMonth;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Format\Exception\FormatterError;

final readonly class ParsedDate
{
    use ParsedTrait;

    public function __construct(
        private int|null $year = null,
        private int|null $month = null,
        private int|null $day = null,
    ) {
    }

    /**
     * @param (callable(): Year)|null $fallback
     *
     * @throws FormatterError
     */
    public function year(?callable $fallback = null): Year
    {
        return self::wrap('Year', Year::of(...), $fallback, $this->year);
    }

    /**
     * @param (callable(): Month)|null $fallback
     *
     * @throws FormatterError
     */
    public function month(?callable $fallback = null): Month
    {
        return self::wrap('Month', Month::of(...), $fallback, $this->month);
    }

    /**
     * @param (callable(): DayOfMonth)|null $fallback
     *
     * @throws FormatterError
     */
    public function day(?callable $fallback = null): DayOfMonth
    {
        return self::wrap('DayOfMonth', DayOfMonth::of(...), $fallback, $this->day);
    }
}
