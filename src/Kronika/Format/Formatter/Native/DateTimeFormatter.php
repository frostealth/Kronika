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

use DateTimeInterface as Native;
use Kronika\DateTime;
use Kronika\Format\DateTime\Formatted;
use Kronika\Format\DateTime\FormattedLocal;
use Kronika\Format\DateTime\Formatter;
use Kronika\Format\Exception\FormatterError;
use Kronika\Format\Parsed;
use Kronika\LocalDateTime;

final readonly class DateTimeFormatter implements Formatter
{
    #[\Override]
    public function format(DateTime $formattable, string $format): string
    {
        return $formattable->toNative()->format(
            format: $formattable instanceof LocalDateTime ? $this->sanitize($format) : $format,
        );
    }

    #[\Override]
    public function parse(Formatted $formatted): Parsed
    {
        $isLocal = $formatted instanceof FormattedLocal;
        $format = $isLocal ? $this->sanitize($formatted->format()) : $formatted->format();

        $native = \DateTimeImmutable::createFromFormat($format, $formatted->value());
        if ($native === false && $formatted instanceof FormattedLocal) {
            $native = \DateTimeImmutable::createFromFormat($formatted->format(), $formatted->value());
        }
        if (! $native instanceof Native) {
            throw new FormatterError("Failed to parse date-time string [{$formatted->value()}]");
        }

        [$year, $month, $day, $hour, $minute, $second, $micro, $timezone] = \sscanf(
            $native->format('Y-m-d H:i:s.u e'),
            format: '%d-%u-%u %u:%u:%u.%u %s',
        );

        return new Parsed(
            date: new Parsed\ParsedDate(year: $year, month: $month, day: $day),
            time: new Parsed\ParsedTime(hour: $hour, minute: $minute, second: $second, micro: $micro),
            timezone: $isLocal ? null : $timezone,
        );
    }

    /** @return non-empty-string */
    private function sanitize(string $format): string
    {
        $sanitized = \preg_replace('/(?<!\\\\)([eOPpTZ])/', '', $format);
        if (! \is_string($sanitized)) {
            throw new FormatterError("Invalid date-time format string [$format]");
        }

        $sanitized = \trim($sanitized, ' ');
        if ($sanitized === '') {
            throw new FormatterError("Invalid date-time format string [$format]");
        }

        return $sanitized;
    }
}
