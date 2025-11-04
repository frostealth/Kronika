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

namespace Kronika\Exception;

/**
 * The exception thrown if an error occurs during formatting to a string
 * or parsing a string from a given format.
 */
class FormatError extends RuntimeException
{
}
