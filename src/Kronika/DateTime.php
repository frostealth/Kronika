<?php

declare(strict_types=1);

namespace Kronika;

use Kronika\Date\DayOfMonth;
use Kronika\Date\DayOfWeek;
use Kronika\Date\Month;
use Kronika\Date\Year;
use Kronika\Time\Hour;
use Kronika\Time\Minute;
use Kronika\Time\Second;

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

    public function add(Duration $interval): static;

    public function sub(Duration $interval): static;

    public function until(self $end): Duration;

    /**
     * @param non-empty-string $format
     *
     * @return non-empty-string
     */
    public function format(string $format): string;

    /** @param non-empty-string $modifier */
    public function modify(string $modifier): static;

    public function toStartOfMonth(): static;

    public function toEndOfMonth(): static;

    public function toNative(): \DateTimeImmutable;

    public function toNativeMutable(): \DateTime;

    public function instant(): Instant;

    /** @return non-empty-string */
    public function __toString(): string;
}
