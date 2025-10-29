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

namespace Kronika;

use Kronika\Utils\Compared;

/**
 * Represents a unit of date-time.
 *
 * @internal
 */
interface Unit
{
    /** @internal {@see DateTime::compareTo()} */
    public function _compareInDateTime(DateTime $that, Precision $precision): Compared;

    /** @internal {@see DateTime::until()} */
    public function _untilInDateTime(DateTime $start): Duration;

    /** @internal {@see DateTime::with()} */
    public function _withinDateTime(LocalDateTime $dateTime): LocalDateTime;
}
