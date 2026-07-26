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
use Kronika\OverflowMode;
use Kronika\Time\TimeUnit as Unit;
use Kronika\Utils\RescueTrait;

/**
 * @psalm-require-implements Unit
 *
 * @psalm-internal Kronika\Time
 * @internal
 *
 * @method static static|null tryOf(mixed $value)
 */
trait TimeUnit
{
    use RescueTrait;

    /** @internal {@see \Kronika\DateTime::with()} */
    #[\Override]
    final public function _withinDateTime(LocalDateTime $datetime, OverflowMode $mode): LocalDateTime
    {
        return $datetime->with($datetime->time()->with($this), $mode);
    }
}
