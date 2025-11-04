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

namespace Kronika\Format\DateTime;

use Kronika\DateTime;
use Kronika\Format\Parsed;

interface Formatter
{
    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(DateTime $formattable, string $format): string;

    public function parse(Formatted $formatted): Parsed;
}
