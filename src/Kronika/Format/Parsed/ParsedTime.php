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

use Kronika\Format\Exception\FormatterError;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;

final readonly class ParsedTime
{
    use ParsedTrait;

    public function __construct(
        private int|null $hour = null,
        private int|null $minute = null,
        private int|null $second = null,
        private int|null $micro = null,
    ) {
    }

    /**
     * @param (callable(): Hour)|null $fallback
     *
     * @throws FormatterError
     */
    public function hour(?callable $fallback = null): Hour
    {
        return self::wrap('Hour', Hour::of(...), $fallback, $this->hour);
    }

    /**
     * @param (callable(): Minute)|null $fallback
     *
     * @throws FormatterError
     */
    public function minute(?callable $fallback = null): Minute
    {
        return self::wrap('Minute', Minute::of(...), $fallback, $this->minute);
    }

    /**
     * @param (callable(): Second)|null $fallback
     *
     * @throws FormatterError
     */
    public function second(?callable $fallback = null): Second
    {
        return self::wrap('Second', Second::of(...), $fallback, second: $this->second, micro: $this->micro ?? 0);
    }
}
