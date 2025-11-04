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
        )->format($this->satinize($format));
    }

    #[\Override]
    public function parse(Formatted $formatted): Parsed
    {
        $native = $this->native($this->satinize($formatted->format()), $formatted->string());
        [$hour, $minute, $second, $micro] = \sscanf($native->format('H:i:s.u'), '%u:%u:%u.%u');

        return new Parsed(hour: $hour, minute: $minute, second: $second, micro: $micro);
    }

    private function satinize(string $format): string
    {
        return \preg_replace('/(?<!\\\\)([^AaBGgHhisuv:\\\\\s\d-])/', '\\\\$1', $format);
    }

    private function native(string $format, string $string): \DateTimeInterface
    {
        $native = \DateTimeImmutable::createFromFormat($format, $string);
        if (! $native instanceof \DateTimeImmutable) {
            throw new \RuntimeException("Failed to parse [$string]");
        }

        return $native;
    }
}
