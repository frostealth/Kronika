<?php

declare(strict_types=1);

namespace Kronika;

if (! \function_exists('now')) {
    function now(?\DateTimeZone $timezone = null): ZonedDateTime
    {
        return ZonedDateTime::ofDateTime(new \DateTimeImmutable('now', $timezone));
    }
}
