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

        $native = \DateTimeImmutable::createFromFormat($format, $formatted->string());
        if ($native === false && $formatted instanceof FormattedLocal) {
            $native = \DateTimeImmutable::createFromFormat($formatted->format(), $formatted->string());
        }
        if (! $native instanceof Native) {
            throw new \RuntimeException("Failed to parse [{$formatted->string()}]");
        }

        return new Parsed(...\sscanf(
            $native->format('Y-m-d H:i:s.u e'),
            format: '%d-%u-%u %u:%u:%u.%u %s',
        ));
    }

    private function sanitize(string $format): string
    {
        return \trim(\preg_replace('/(?<!\\\\)([eOPpTZ])/', '', $format), ' ');
    }
}
