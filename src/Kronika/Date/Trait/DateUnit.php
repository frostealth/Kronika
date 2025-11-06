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

namespace Kronika\Date\Trait;

use Kronika\Date\DateUnit as Unit;
use Kronika\LocalDateTime;

/**
 * @psalm-require-implements Unit
 *
 * @psalm-internal Kronika\Date
 * @internal
 */
trait DateUnit
{
    /** @internal {@see \Kronika\DateTime::with()} */
    #[\Override]
    final public function _withinDateTime(LocalDateTime $datetime, bool $rolling): LocalDateTime
    {
        return $datetime->with($datetime->date()->with($this, $rolling), $rolling);
    }
}
