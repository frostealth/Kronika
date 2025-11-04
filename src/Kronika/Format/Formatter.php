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

namespace Kronika\Format;

use Kronika\Date;
use Kronika\DateTime;
use Kronika\Format\Date\Formatted as FormattedDate;
use Kronika\Format\Date\Formatter as DateFormatter;
use Kronika\Format\DateTime\Formatted as FormattedDateTime;
use Kronika\Format\DateTime\Formatter as DateTimeFormatter;
use Kronika\Format\Time\Formatted as FormattedTime;
use Kronika\Format\Time\Formatter as TimeFormatter;
use Kronika\Time;

final readonly class Formatter implements DateTimeFormatter, DateFormatter, TimeFormatter
{
    public function __construct(
        private DateTimeFormatter $datetime,
        private DateFormatter $date,
        private TimeFormatter $time,
    ) {
    }

    #[\Override]
    public function format(DateTime|Date|Time $formattable, string $format): string
    {
        return match(true) {
            $formattable instanceof DateTime => $this->datetime->format($formattable, $format),
            $formattable instanceof Date => $this->date->format($formattable, $format),
            $formattable instanceof Time => $this->time->format($formattable, $format),
        };
    }

    #[\Override]
    public function parse(Formatted $formatted): Parsed
    {
        return match(true) {
            $formatted instanceof FormattedDateTime => $this->datetime->parse($formatted),
            $formatted instanceof FormattedDate => $this->date->parse($formatted),
            $formatted instanceof FormattedTime => $this->time->parse($formatted),
            default => throw new \UnexpectedValueException(\sprintf(
                'Unexpected type [%s]',
                $formatted::class,
            )),
        };
    }
}
