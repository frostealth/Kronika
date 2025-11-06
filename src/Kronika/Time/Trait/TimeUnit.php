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

namespace Kronika\Time\Trait;

use Kronika\LocalDateTime;
use Kronika\Time\TimeUnit as Unit;

/**
 * @psalm-require-implements Unit
 *
 * @psalm-internal Kronika\Time
 * @internal
 */
trait TimeUnit
{
    /** @internal {@see \Kronika\DateTime::with()} */
    #[\Override]
    final public function _withinDateTime(LocalDateTime $datetime): LocalDateTime
    {
        return $datetime->with($datetime->time()->with($this));
    }
}
