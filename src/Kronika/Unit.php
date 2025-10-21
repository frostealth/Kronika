<?php

declare(strict_types=1);

namespace Kronika;

/**
 * @internal
 */
interface Unit
{
    /** @internal */
    public function withinDateTime(LocalDateTime $dateTime): LocalDateTime;
}
