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

namespace Kronika\Date\Trait;

use Kronika\DateTime;

/**
 * @internal
 * @psalm-require-implements DateTime
 */
trait HasDate
{
    /**
     * Moves backward to the previous year.
     *
     * @see \Kronika\Date::previousYear()
     */
    final public function previousYear(): static
    {
        return $this->with($this->date()->previousYear());
    }

    /**
     * Moves forward to the next year.
     *
     * @see \Kronika\Date::nextYear()
     */
    final public function nextYear(): static
    {
        return $this->with($this->date()->nextYear());
    }

    /**
     * Moves to the first day of the current year.
     *
     * @see \Kronika\Date::startOfYear()
     */
    final public function startOfYear(): static
    {
        return $this->with($this->date()->startOfYear());
    }

    /**
     * Moves to the last day of the current year.
     *
     * @see \Kronika\Date::endOfYear()
     */
    final public function endOfYear(): static
    {
        return $this->with($this->date()->endOfYear());
    }

    /**
     * Moves backward to the previous month.
     *
     * @see \Kronika\Date::previousMonth()
     */
    final public function previousMonth(): static
    {
        return $this->with($this->date()->previousMonth());
    }

    /**
     * Moves forward to the next month.
     *
     * @see \Kronika\Date::nextMonth()
     */
    final public function nextMonth(): static
    {
        return $this->with($this->date()->nextMonth());
    }

    /**
     * Moves to the first day of the current month.
     *
     * @see \Kronika\Date::startOfMonth()
     */
    final public function startOfMonth(): static
    {
        return $this->with($this->date()->startOfMonth());
    }

    /**
     * Moves to the last day of the current month.
     *
     * @see \Kronika\Date::endOfMonth()
     */
    final public function endOfMonth(): static
    {
        return $this->with($this->date()->endOfMonth());
    }

    /**
     * Moves backward to the previous week.
     *
     * @see \Kronika\Date::previousWeek()
     */
    final public function previousWeek(): static
    {
        return $this->with($this->date()->previousWeek());
    }

    /**
     * Moves forward to the next week.
     *
     * @see \Kronika\Date::nextWeek()
     */
    final public function nextWeek(): static
    {
        return $this->with($this->date()->nextWeek());
    }

    /**
     * Moves backward to the previous day.
     *
     * @see \Kronika\Date::previousDay()
     */
    final public function previousDay(): static
    {
        return $this->with($this->date()->previousDay());
    }

    /**
     * Moves forward to the next day.
     *
     * @see \Kronika\Date::nextDay()
     */
    final public function nextDay(): static
    {
        return $this->with($this->date()->nextDay());
    }
}
