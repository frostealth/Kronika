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

if (! \function_exists('now')) {
    function now(?\DateTimeZone $timezone = null): ZonedDateTime
    {
        return ZonedDateTime::ofDateTime(new \DateTimeImmutable(timezone: $timezone));
    }
}
