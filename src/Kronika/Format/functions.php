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

namespace Kronika\Format;

if (! \function_exists('\\Kronika\\Format\\native')) {
    /**
     * Returns an instance of the native formater.
     * {@see \DateTimeInterface::format()} syntax.
     */
    function native(): Formatter
    {
        static $native = new Formatter(
            datetime: new Formatter\Native\DateTimeFormatter(),
            date: new Formatter\Native\DateFormatter(),
            time: new Formatter\Native\TimeFormatter(),
        );

        return $native;
    }
}
