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

use Kronika\Date;
use Kronika\Format\Date\Formatted;
use Kronika\Format\Date\Formatter;
use Kronika\Format\Exception\FormatterError;
use Kronika\Format\Parsed;
use Kronika\Time;

final readonly class DateFormatter implements Formatter
{
    #[\Override]
    public function format(Date $formattable, string $format): string
    {
        return $formattable->at(Time::midnight())->toNative()->format($this->sanitize($format));
    }

    #[\Override]
    public function parse(Formatted $formatted): Parsed
    {
        $native = $this->native($this->sanitize($formatted->format()), $formatted->value());
        [$year, $month, $day] = \sscanf($native->format('Y-m-d'), '%d-%u-%u');

        return new Parsed(date: new Parsed\ParsedDate(year: $year, month: $month, day: $day));
    }

    /** @return non-empty-string */
    private function sanitize(string $format): string
    {
        $sanitized = \preg_replace('/(?<!\\\\)([^DdjlNSWwzFMmntLoXxYy:\\\\\s\d-])/', '\\\\$1', $format);
        if (! \is_string($sanitized) || $sanitized === '') {
            throw new FormatterError("Invalid date format string [$format]");
        }

        return $sanitized;
    }

    private function native(string $format, string $string): \DateTimeInterface
    {
        $native = \DateTimeImmutable::createFromFormat($format, $string);
        if (! $native instanceof \DateTimeImmutable) {
            throw new FormatterError("Failed to parse date string [$string]");
        }

        return $native;
    }
}
