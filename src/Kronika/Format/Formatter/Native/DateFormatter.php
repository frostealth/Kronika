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
        $this->assertNotEmpty($format, 'Format');

        return new \DateTimeImmutable((string)$formattable)->format($this->sanitize($format));
    }

    #[\Override]
    public function parse(Formatted $formatted): Parsed
    {
        $this->assertNotEmpty($formatted->format(), 'Format');
        $this->assertNotEmpty($formatted->value(), 'Date');

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

    private function native(string $format, string $value): \DateTimeInterface
    {
        $native = \DateTimeImmutable::createFromFormat($format, $value);
        if (! $native instanceof \DateTimeImmutable) {
            throw new FormatterError("Failed to parse date string [$value]");
        }

        return $native;
    }

    /** @throws FormatterError */
    private function assertNotEmpty(string $value, string $unit): void
    {
        if (\trim($value) === '') {
            throw new FormatterError("$unit string cannot be empty");
        }
    }
}
