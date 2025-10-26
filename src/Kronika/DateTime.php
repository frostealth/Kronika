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

namespace Kronika;

use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;
use Kronika\Utils\Comparison;

interface DateTime
{
    public function date(): Date;

    public function year(): Year;

    public function month(): Month;

    public function day(): DayOfMonth;

    public function dayOfWeek(): DayOfWeek;

    public function time(): Time;

    public function hour(): Hour;

    public function minute(): Minute;

    public function second(): Second;

    public function with(Unit $unit): static;

    public function resetMicro(): static;

    public function resetSecond(): static;

    public function add(Duration $interval): static;

    public function sub(Duration $interval): static;

    public function until(self $end): Duration;

    public function isBefore(DateTime $other, Precision $precision = Precision::Micro): bool;

    public function isBeforeOrEqual(DateTime $other, Precision $precision = Precision::Micro): bool;

    public function isEqualTo(DateTime $other, Precision $precision = Precision::Micro): bool;

    public function isNotEqualTo(DateTime $other, Precision $precision = Precision::Micro): bool;

    public function isAfterOrEqual(DateTime $other, Precision $precision = Precision::Micro): bool;

    public function isAfter(DateTime $other, Precision $precision = Precision::Micro): bool;

    public function compareTo(DateTime $other, Precision $precision = Precision::Micro): Comparison;

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string;

    public function toNative(): \DateTimeImmutable;

    public function toNativeMutable(): \DateTime;

    public function instant(): Instant;

    /** @return non-empty-string */
    public function __toString(): string;
}
