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

namespace Kronika\Format\Formatter\Native;

use Kronika\Format\Exception\FormatterError;
use Kronika\Format\Parsed;
use Kronika\Format\Time\Formatted;
use Kronika\Format\Time\Formatter;
use Kronika\Time;

final readonly class TimeFormatter implements Formatter
{
    #[\Override]
    public function format(Time $formattable, string $format): string
    {
        return \DateTimeImmutable::createFromTimestamp(
            $formattable->instant()->value(),
        )->format($this->sanitize($format));
    }

    #[\Override]
    public function parse(Formatted $formatted): Parsed
    {
        $native = $this->native($this->sanitize($formatted->format()), $formatted->value());
        [$hour, $minute, $second, $micro] = \sscanf($native->format('H:i:s.u'), '%u:%u:%u.%u');

        return new Parsed(time: new Parsed\ParsedTime(hour: $hour, minute: $minute, second: $second, micro: $micro));
    }

    /** @return non-empty-string */
    private function sanitize(string $format): string
    {
        $sanitized = \preg_replace('/(?<!\\\\)([^AaBGgHhisuv:\\\\\s\d-])/', '\\\\$1', $format);
        if (! \is_string($sanitized) || $sanitized === '') {
            throw new FormatterError("Invalid time format string [$format]");
        }

        return $sanitized;
    }

    private function native(string $format, string $value): \DateTimeInterface
    {
        $native = \DateTimeImmutable::createFromFormat($format, $value);
        if (! $native instanceof \DateTimeImmutable) {
            throw new FormatterError("Failed to parse time string [$value]");
        }

        return $native;
    }
}
